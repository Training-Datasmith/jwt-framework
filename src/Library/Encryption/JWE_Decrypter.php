<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Key_Checker;
use Jose\Component\Encryption\Algorithm\Content_Encryption_Algorithm;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Direct_Encryption;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Agreement;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Agreement_With_Key_Wrapping;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Encryption;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Wrapping;
use Jose\Component\Encryption\Algorithm\Key_Encryption_Algorithm;
use function sprintf;
use function strlen;
use Throwable;
class Jwe_Decrypter
{
    private readonly Algorithm_Manager $key_encryption_algorithm_manager;
    private readonly Algorithm_Manager $content_encryption_algorithm_manager;
    public function __construct(Algorithm_Manager $algorithm_manager)
    {
        $key_encryption_algorithms = [];
        $content_encryption_algorithms = [];
        foreach ($algorithm_manager->all() as $key => $algorithm) {
            if ($algorithm instanceof Key_Encryption_Algorithm) {
                $key_encryption_algorithms[$key] = $algorithm;
            }
            if ($algorithm instanceof Content_Encryption_Algorithm) {
                $content_encryption_algorithms[$key] = $algorithm;
            }
        }
        $this->key_encryption_algorithm_manager = new Algorithm_Manager($key_encryption_algorithms);
        $this->content_encryption_algorithm_manager = new Algorithm_Manager($content_encryption_algorithms);
    }
    /**
     * Returns the key encryption algorithm manager.
     */
    public function get_key_encryption_algorithm_manager(): Algorithm_Manager
    {
        return $this->key_encryption_algorithm_manager;
    }
    /**
     * Returns the content encryption algorithm manager.
     */
    public function get_content_encryption_algorithm_manager(): Algorithm_Manager
    {
        return $this->content_encryption_algorithm_manager;
    }
    /**
     * This method will try to decrypt the given JWE and recipient using a JWK.
     *
     * @param JWE $jwe A JWE object to decrypt
     * @param JWK $jwk The key used to decrypt the input
     * @param int $recipient The recipient used to decrypt the token
     */
    public function decrypt_using_key(JWE &$jwe, JWK $jwk, int $recipient, ?JWK $sender_key = null): bool
    {
        $jwkset = new Jwk_Set([$jwk]);
        return $this->decrypt_using_key_set($jwe, $jwkset, $recipient, $sender_key);
    }
    /**
     * This method will try to decrypt the given JWE and recipient using a JWKSet.
     *
     * @param JWE $jwe A JWE object to decrypt
     * @param JWKSet $jwkset The key set used to decrypt the input
     * @param JWK $jwk The key used to decrypt the token in case of success
     * @param int $recipient The recipient used to decrypt the token in case of success
     */
    public function decrypt_using_key_set(JWE &$jwe, Jwk_Set $jwkset, int $recipient, ?JWK &$jwk = null, ?JWK $sender_key = null): bool
    {
        if ($jwkset->count() === 0) {
            throw new InvalidArgumentException('No key in the key set.');
        }
        if ($jwe->get_payload() !== null) {
            return true;
        }
        if ($jwe->count_recipients() === 0) {
            throw new InvalidArgumentException('The JWE does not contain any recipient.');
        }
        $plaintext = $this->decrypt_recipient_key($jwe, $jwkset, $recipient, $jwk, $sender_key);
        if ($plaintext !== null) {
            $jwe = $jwe->with_payload($plaintext);
            return true;
        }
        return false;
    }
    private function decrypt_recipient_key(JWE $jwe, Jwk_Set $jwkset, int $i, ?JWK &$success_jwk = null, ?JWK $sender_key = null): ?string
    {
        $recipient = $jwe->get_recipient($i);
        $complete_header = array_merge($jwe->get_shared_protected_header(), $jwe->get_shared_header(), $recipient->get_header());
        $this->check_complete_header($complete_header);
        $key_encryption_algorithm = $this->get_key_encryption_algorithm($complete_header);
        $content_encryption_algorithm = $this->get_content_encryption_algorithm($complete_header);
        $this->check_iv_size($jwe->get_iv(), $content_encryption_algorithm->get_iv_size());
        foreach ($jwkset as $recipient_key) {
            try {
                Key_Checker::check_key_usage($recipient_key, 'decryption');
                if ($key_encryption_algorithm->name() !== 'dir') {
                    Key_Checker::check_key_algorithm($recipient_key, $key_encryption_algorithm->name());
                } else {
                    Key_Checker::check_key_algorithm($recipient_key, $content_encryption_algorithm->name());
                }
                $cek = $this->decrypt_cek($key_encryption_algorithm, $content_encryption_algorithm, $recipient_key, $sender_key, $recipient, $complete_header);
                $this->check_cek_size($cek, $key_encryption_algorithm, $content_encryption_algorithm);
                $payload = $this->decrypt_payload($jwe, $cek, $content_encryption_algorithm);
                $success_jwk = $recipient_key;
                return $payload;
            } catch (Throwable) {
                //We do nothing, we continue with other keys
                continue;
            }
        }
        return null;
    }
    private function check_cek_size(string $cek, Key_Encryption_Algorithm $key_encryption_algorithm, Content_Encryption_Algorithm $algorithm): void
    {
        if ($key_encryption_algorithm instanceof Direct_Encryption || $key_encryption_algorithm instanceof Key_Agreement) {
            return;
        }
        if (strlen($cek) * 8 !== $algorithm->get_cek_size()) {
            throw new InvalidArgumentException('Invalid CEK size');
        }
    }
    private function check_iv_size(?string $iv, int $required_iv_size): void
    {
        if ($iv === null && $required_iv_size !== 0) {
            throw new InvalidArgumentException('Invalid IV size');
        }
        if (is_string($iv) && strlen($iv) !== $required_iv_size / 8) {
            throw new InvalidArgumentException('Invalid IV size');
        }
    }
    private function decrypt_cek(Algorithm $key_encryption_algorithm, Content_Encryption_Algorithm $content_encryption_algorithm, JWK $recipient_key, ?JWK $sender_key, Recipient $recipient, array $complete_header): string
    {
        if ($key_encryption_algorithm instanceof Direct_Encryption) {
            return $key_encryption_algorithm->get_cek($recipient_key);
        }
        if ($key_encryption_algorithm instanceof Key_Agreement) {
            return $key_encryption_algorithm->get_agreement_key($content_encryption_algorithm->get_cek_size(), $content_encryption_algorithm->name(), $recipient_key, $sender_key, $complete_header);
        }
        if ($key_encryption_algorithm instanceof Key_Agreement_With_Key_Wrapping) {
            return $key_encryption_algorithm->unwrap_agreement_key($recipient_key, $sender_key, $recipient->get_encrypted_key() ?? '', $content_encryption_algorithm->get_cek_size(), $complete_header);
        }
        if ($key_encryption_algorithm instanceof Key_Encryption) {
            return $key_encryption_algorithm->decrypt_key($recipient_key, $recipient->get_encrypted_key() ?? '', $complete_header);
        }
        if ($key_encryption_algorithm instanceof Key_Wrapping) {
            return $key_encryption_algorithm->unwrap_key($recipient_key, $recipient->get_encrypted_key() ?? '', $complete_header);
        }
        throw new InvalidArgumentException('Unsupported CEK generation');
    }
    private function decrypt_payload(JWE $jwe, string $cek, Content_Encryption_Algorithm $content_encryption_algorithm): string
    {
        return $content_encryption_algorithm->decrypt_content($jwe->get_ciphertext() ?? '', $cek, $jwe->get_iv() ?? '', $jwe->get_aad(), $jwe->get_encoded_shared_protected_header(), $jwe->get_tag() ?? '');
    }
    private function check_complete_header(array $complete_headers): void
    {
        foreach (['enc', 'alg'] as $key) {
            if (!isset($complete_headers[$key])) {
                throw new InvalidArgumentException(sprintf("Parameter '%s' is missing.", $key));
            }
        }
    }
    private function get_key_encryption_algorithm(array $complete_headers): Key_Encryption_Algorithm
    {
        $key_encryption_algorithm = $this->key_encryption_algorithm_manager->get($complete_headers['alg']);
        if (!$key_encryption_algorithm instanceof Key_Encryption_Algorithm) {
            throw new InvalidArgumentException(sprintf('The key encryption algorithm "%s" is not supported or does not implement KeyEncryptionAlgorithm interface.', $complete_headers['alg']));
        }
        return $key_encryption_algorithm;
    }
    private function get_content_encryption_algorithm(array $complete_header): Content_Encryption_Algorithm
    {
        $content_encryption_algorithm = $this->content_encryption_algorithm_manager->get($complete_header['enc']);
        if (!$content_encryption_algorithm instanceof Content_Encryption_Algorithm) {
            throw new InvalidArgumentException(sprintf('The key encryption algorithm "%s" is not supported or does not implement the ContentEncryption interface.', $complete_header['enc']));
        }
        return $content_encryption_algorithm;
    }
}