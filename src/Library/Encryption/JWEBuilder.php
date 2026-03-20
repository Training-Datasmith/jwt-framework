<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use function array_key_exists;
use function count;
use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Core\Util\Key_Checker;
use Jose\Component\Encryption\Algorithm\Content_Encryption_Algorithm;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Direct_Encryption;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Agreement;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Agreement_With_Key_Wrapping;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Encryption;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Key_Wrapping;
use Jose\Component\Encryption\Algorithm\Key_Encryption_Algorithm;
use LogicException;
use RuntimeException;
use function sprintf;
class Jwe_Builder
{
    protected ?JWK $sender_key = null;
    protected ?string $payload = null;
    protected ?string $aad = null;
    protected array $recipients = [];
    protected array $shared_protected_header = [];
    protected array $shared_header = [];
    private ?string $key_management_mode = null;
    private ?Content_Encryption_Algorithm $content_encryption_algorithm = null;
    private readonly Algorithm_Manager $key_encryption_algorithm_manager;
    private readonly Algorithm_Manager $content_encryption_algorithm_manager;
    public function __construct(Algorithm_Manager $algorithm_manager)
    {
        $key_encryption_algorithms = [];
        $content_encryption_algorithms = [];
        foreach ($algorithm_manager->all() as $algorithm) {
            if ($algorithm instanceof Key_Encryption_Algorithm) {
                $key_encryption_algorithms[] = $algorithm;
            }
            if ($algorithm instanceof Content_Encryption_Algorithm) {
                $content_encryption_algorithms[] = $algorithm;
            }
        }
        $this->key_encryption_algorithm_manager = new Algorithm_Manager($key_encryption_algorithms);
        $this->content_encryption_algorithm_manager = new Algorithm_Manager($content_encryption_algorithms);
    }
    /**
     * Reset the current data.
     */
    public function create(): self
    {
        $this->sender_key = null;
        $this->payload = null;
        $this->aad = null;
        $this->recipients = [];
        $this->shared_protected_header = [];
        $this->shared_header = [];
        $this->key_management_mode = null;
        return $this;
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
     * Set the payload of the JWE to build.
     */
    public function with_payload(string $payload): self
    {
        $clone = clone $this;
        $clone->payload = $payload;
        return $clone;
    }
    /**
     * Set the Additional Authenticated Data of the JWE to build.
     */
    public function with_aad(?string $aad): self
    {
        $clone = clone $this;
        $clone->aad = $aad;
        return $clone;
    }
    /**
     * Set the shared protected header of the JWE to build.
     */
    public function with_shared_protected_header(array $shared_protected_header): self
    {
        $this->check_duplicated_header_parameters($shared_protected_header, $this->shared_header);
        foreach ($this->recipients as $recipient) {
            $this->check_duplicated_header_parameters($shared_protected_header, $recipient->get_header());
        }
        $clone = clone $this;
        $clone->shared_protected_header = $shared_protected_header;
        return $clone;
    }
    /**
     * Set the shared header of the JWE to build.
     */
    public function with_shared_header(array $shared_header): self
    {
        $this->check_duplicated_header_parameters($this->shared_protected_header, $shared_header);
        foreach ($this->recipients as $recipient) {
            $this->check_duplicated_header_parameters($shared_header, $recipient->get_header());
        }
        $clone = clone $this;
        $clone->shared_header = $shared_header;
        return $clone;
    }
    /**
     * Adds a recipient to the JWE to build.
     */
    public function add_recipient(JWK $recipient_key, array $recipient_header = []): self
    {
        $this->check_duplicated_header_parameters($this->shared_protected_header, $recipient_header);
        $this->check_duplicated_header_parameters($this->shared_header, $recipient_header);
        $clone = clone $this;
        $complete_header = array_merge($clone->shared_header, $recipient_header, $clone->shared_protected_header);
        $clone->check_and_set_content_encryption_algorithm($complete_header);
        $key_encryption_algorithm = $clone->get_key_encryption_algorithm($complete_header);
        if ($clone->key_management_mode === null) {
            $clone->key_management_mode = $key_encryption_algorithm->get_key_management_mode();
        } else if (!$clone->are_key_management_modes_compatible($clone->key_management_mode, $key_encryption_algorithm->get_key_management_mode())) {
            throw new InvalidArgumentException('Foreign key management mode forbidden.');
        }
        $clone->check_key($key_encryption_algorithm, $recipient_key);
        $clone->recipients[] = ['key' => $recipient_key, 'header' => $recipient_header, 'key_encryption_algorithm' => $key_encryption_algorithm];
        return $clone;
    }
    //TODO: Verify if the key is compatible with the key encryption algorithm like is done to the ECDH-ES
    /**
     * Set the sender JWK to be used instead of the internal generated JWK
     */
    public function with_sender_key(JWK $sender_key): self
    {
        $clone = clone $this;
        $complete_header = array_merge($clone->shared_header, $clone->shared_protected_header);
        $key_encryption_algorithm = $clone->get_key_encryption_algorithm($complete_header);
        if ($clone->key_management_mode === null) {
            $clone->key_management_mode = $key_encryption_algorithm->get_key_management_mode();
        } else if (!$clone->are_key_management_modes_compatible($clone->key_management_mode, $key_encryption_algorithm->get_key_management_mode())) {
            throw new InvalidArgumentException('Foreign key management mode forbidden.');
        }
        $clone->check_key($key_encryption_algorithm, $sender_key);
        $clone->sender_key = $sender_key;
        return $clone;
    }
    /**
     * Builds the JWE.
     */
    public function build(): JWE
    {
        if ($this->payload === null) {
            throw new LogicException('Payload not set.');
        }
        if (count($this->recipients) === 0) {
            throw new LogicException('No recipient.');
        }
        $additional_header = [];
        $cek = $this->determine_cek($additional_header);
        $recipients = [];
        foreach ($this->recipients as $recipient) {
            $recipient = $this->process_recipient($recipient, $cek, $additional_header);
            $recipients[] = $recipient;
        }
        if ((is_countable($additional_header) ? count($additional_header) : 0) !== 0 && count($this->recipients) === 1) {
            $shared_protected_header = array_merge($additional_header, $this->shared_protected_header);
        } else {
            $shared_protected_header = $this->shared_protected_header;
        }
        $encoded_shared_protected_header = count($shared_protected_header) === 0 ? '' : Base64url_Safe::encode_unpadded(Json_Converter::encode($shared_protected_header));
        [$ciphertext, $iv, $tag] = $this->encrypt_jwe($cek, $encoded_shared_protected_header);
        return new JWE($ciphertext, $iv, $tag, $this->aad, $this->shared_header, $shared_protected_header, $encoded_shared_protected_header, $recipients);
    }
    private function check_and_set_content_encryption_algorithm(array $complete_header): void
    {
        $content_encryption_algorithm = $this->get_content_encryption_algorithm($complete_header);
        if ($this->content_encryption_algorithm === null) {
            $this->content_encryption_algorithm = $content_encryption_algorithm;
        } elseif ($content_encryption_algorithm->name() !== $this->content_encryption_algorithm->name()) {
            throw new InvalidArgumentException('Inconsistent content encryption algorithm');
        }
    }
    private function process_recipient(array $recipient, string $cek, array &$additional_header): Recipient
    {
        $complete_header = array_merge($this->shared_header, $recipient['header'], $this->shared_protected_header);
        $key_encryption_algorithm = $recipient['key_encryption_algorithm'];
        if (!$key_encryption_algorithm instanceof Key_Encryption_Algorithm) {
            throw new InvalidArgumentException('The key encryption algorithm is not valid');
        }
        $encrypted_content_encryption_key = $this->get_encrypted_key($complete_header, $cek, $key_encryption_algorithm, $additional_header, $recipient['key'], $recipient['sender_key'] ?? $this->sender_key ?? null);
        $recipient_header = $recipient['header'];
        if ((is_countable($additional_header) ? count($additional_header) : 0) !== 0 && count($this->recipients) !== 1) {
            $recipient_header = array_merge($recipient_header, $additional_header);
            $additional_header = [];
        }
        return new Recipient($recipient_header, $encrypted_content_encryption_key);
    }
    private function encrypt_jwe(string $cek, string $encoded_shared_protected_header): array
    {
        if (!$this->content_encryption_algorithm instanceof Content_Encryption_Algorithm) {
            throw new InvalidArgumentException('The content encryption algorithm is not valid');
        }
        $iv_size = $this->content_encryption_algorithm->get_iv_size();
        $iv = $this->create_iv($iv_size);
        $payload = $this->payload;
        $tag = null;
        $ciphertext = $this->content_encryption_algorithm->encrypt_content($payload ?? '', $cek, $iv, $this->aad, $encoded_shared_protected_header, $tag);
        return [$ciphertext, $iv, $tag];
    }
    private function get_encrypted_key(array $complete_header, string $cek, Key_Encryption_Algorithm $key_encryption_algorithm, array &$additional_header, JWK $recipient_key, ?JWK $sender_key): ?string
    {
        if ($key_encryption_algorithm instanceof Key_Encryption) {
            return $this->get_encrypted_key_from_key_encryption_algorithm($complete_header, $cek, $key_encryption_algorithm, $recipient_key, $additional_header);
        }
        if ($key_encryption_algorithm instanceof Key_Wrapping) {
            return $this->get_encrypted_key_from_key_wrapping_algorithm($complete_header, $cek, $key_encryption_algorithm, $recipient_key, $additional_header);
        }
        if ($key_encryption_algorithm instanceof Key_Agreement_With_Key_Wrapping) {
            return $this->get_encrypted_key_from_key_agreement_and_key_wrapping_algorithm($complete_header, $cek, $key_encryption_algorithm, $additional_header, $recipient_key, $sender_key);
        }
        if ($key_encryption_algorithm instanceof Key_Agreement) {
            return null;
        }
        if ($key_encryption_algorithm instanceof Direct_Encryption) {
            return null;
        }
        throw new InvalidArgumentException('Unsupported key encryption algorithm.');
    }
    private function get_encrypted_key_from_key_agreement_and_key_wrapping_algorithm(array $complete_header, string $cek, Key_Agreement_With_Key_Wrapping $key_encryption_algorithm, array &$additional_header, JWK $recipient_key, ?JWK $sender_key): string
    {
        if ($this->content_encryption_algorithm === null) {
            throw new InvalidArgumentException('Invalid content encryption algorithm');
        }
        return $key_encryption_algorithm->wrap_agreement_key($recipient_key, $sender_key, $cek, $this->content_encryption_algorithm->get_cek_size(), $complete_header, $additional_header);
    }
    private function get_encrypted_key_from_key_encryption_algorithm(array $complete_header, string $cek, Key_Encryption $key_encryption_algorithm, JWK $recipient_key, array &$additional_header): string
    {
        return $key_encryption_algorithm->encrypt_key($recipient_key, $cek, $complete_header, $additional_header);
    }
    private function get_encrypted_key_from_key_wrapping_algorithm(array $complete_header, string $cek, Key_Wrapping $key_encryption_algorithm, JWK $recipient_key, array &$additional_header): string
    {
        return $key_encryption_algorithm->wrap_key($recipient_key, $cek, $complete_header, $additional_header);
    }
    private function check_key(Key_Encryption_Algorithm $key_encryption_algorithm, JWK $recipient_key): void
    {
        if ($this->content_encryption_algorithm === null) {
            throw new InvalidArgumentException('Invalid content encryption algorithm');
        }
        Key_Checker::check_key_usage($recipient_key, 'encryption');
        if ($key_encryption_algorithm->name() !== 'dir') {
            Key_Checker::check_key_algorithm($recipient_key, $key_encryption_algorithm->name());
        } else {
            Key_Checker::check_key_algorithm($recipient_key, $this->content_encryption_algorithm->name());
        }
    }
    private function determine_cek(array &$additional_header): string
    {
        if ($this->content_encryption_algorithm === null) {
            throw new InvalidArgumentException('Invalid content encryption algorithm');
        }
        switch ($this->key_management_mode) {
            case Key_Encryption::MODE_ENCRYPT:
            case Key_Encryption::MODE_WRAP:
                return $this->create_cek($this->content_encryption_algorithm->get_cek_size());
            case Key_Encryption::MODE_AGREEMENT:
                if (count($this->recipients) !== 1) {
                    throw new LogicException('Unable to encrypt for multiple recipients using key agreement algorithms.');
                }
                $recipient_key = $this->recipients[0]['key'];
                $sender_key = $this->recipients[0]['sender_key'] ?? null;
                $algorithm = $this->recipients[0]['key_encryption_algorithm'];
                if (!$algorithm instanceof Key_Agreement) {
                    throw new InvalidArgumentException('Invalid content encryption algorithm');
                }
                $complete_header = array_merge($this->shared_header, $this->recipients[0]['header'], $this->shared_protected_header);
                return $algorithm->get_agreement_key($this->content_encryption_algorithm->get_cek_size(), $this->content_encryption_algorithm->name(), $recipient_key, $sender_key, $complete_header, $additional_header);
            case Key_Encryption::MODE_DIRECT:
                if (count($this->recipients) !== 1) {
                    throw new LogicException('Unable to encrypt for multiple recipients using key agreement algorithms.');
                }
                /** @var JWK $key */
                $key = $this->recipients[0]['key'];
                if ($key->get('kty') !== 'oct') {
                    throw new RuntimeException('Wrong key type.');
                }
                $k = $key->get('k');
                if (!is_string($k)) {
                    throw new RuntimeException('Invalid key.');
                }
                return Base64url_Safe::decode_no_padding($k);
            default:
                throw new InvalidArgumentException(sprintf('Unsupported key management mode "%s".', $this->key_management_mode));
        }
    }
    private function are_key_management_modes_compatible(string $current, string $new): bool
    {
        $agree = Key_Encryption_Algorithm::MODE_AGREEMENT;
        $dir = Key_Encryption_Algorithm::MODE_DIRECT;
        $enc = Key_Encryption_Algorithm::MODE_ENCRYPT;
        $wrap = Key_Encryption_Algorithm::MODE_WRAP;
        $supported_key_management_mode_combinations = [$enc . $enc => true, $enc . $wrap => true, $wrap . $enc => true, $wrap . $wrap => true, $agree . $agree => false, $agree . $dir => false, $agree . $enc => false, $agree . $wrap => false, $dir . $agree => false, $dir . $dir => false, $dir . $enc => false, $dir . $wrap => false, $enc . $agree => false, $enc . $dir => false, $wrap . $agree => false, $wrap . $dir => false];
        if (array_key_exists($current . $new, $supported_key_management_mode_combinations)) {
            return $supported_key_management_mode_combinations[$current . $new];
        }
        return false;
    }
    private function create_cek(int $size): string
    {
        return random_bytes($size / 8);
    }
    private function create_iv(int $size): string
    {
        return random_bytes($size / 8);
    }
    private function get_key_encryption_algorithm(array $complete_header): Key_Encryption_Algorithm
    {
        if (!isset($complete_header['alg'])) {
            throw new InvalidArgumentException('Parameter "alg" is missing.');
        }
        $key_encryption_algorithm = $this->key_encryption_algorithm_manager->get($complete_header['alg']);
        if (!$key_encryption_algorithm instanceof Key_Encryption_Algorithm) {
            throw new InvalidArgumentException(sprintf('The key encryption algorithm "%s" is not supported or not a key encryption algorithm instance.', $complete_header['alg']));
        }
        return $key_encryption_algorithm;
    }
    private function get_content_encryption_algorithm(array $complete_header): Content_Encryption_Algorithm
    {
        if (!isset($complete_header['enc'])) {
            throw new InvalidArgumentException('Parameter "enc" is missing.');
        }
        $content_encryption_algorithm = $this->content_encryption_algorithm_manager->get($complete_header['enc']);
        if (!$content_encryption_algorithm instanceof Content_Encryption_Algorithm) {
            throw new InvalidArgumentException(sprintf('The content encryption algorithm "%s" is not supported or not a content encryption algorithm instance.', $complete_header['enc']));
        }
        return $content_encryption_algorithm;
    }
    private function check_duplicated_header_parameters(array $header1, array $header2): void
    {
        $inter = array_intersect_key($header1, $header2);
        if (count($inter) !== 0) {
            throw new InvalidArgumentException(sprintf('The header contains duplicated entries: %s.', implode(', ', array_keys($inter))));
        }
    }
}