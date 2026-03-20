<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use Jose\Component\Checker\Header_Checker_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager;
use RuntimeException;
use Throwable;
/**
 * @see \Jose\Tests\Component\Encryption\JWELoaderTest
 */
class Jwe_Loader
{
    public function __construct(private readonly Jwe_Serializer_Manager $serializer_manager, private readonly Jwe_Decrypter $jwe_decrypter, private readonly ?Header_Checker_Manager $header_checker_manager)
    {
    }
    /**
     * Returns the JWE Decrypter object.
     */
    public function get_jwe_decrypter(): Jwe_Decrypter
    {
        return $this->jwe_decrypter;
    }
    /**
     * Returns the header checker manager if set.
     */
    public function get_header_checker_manager(): ?Header_Checker_Manager
    {
        return $this->header_checker_manager;
    }
    /**
     * Returns the serializer manager.
     */
    public function get_serializer_manager(): Jwe_Serializer_Manager
    {
        return $this->serializer_manager;
    }
    /**
     * This method will try to load and decrypt the given token using a JWK. If succeeded, the methods will populate the
     * $recipient variable and returns the JWE.
     */
    public function load_and_decrypt_with_key(string $token, JWK $key, ?int &$recipient): JWE
    {
        $keyset = new Jwk_Set([$key]);
        return $this->load_and_decrypt_with_key_set($token, $keyset, $recipient);
    }
    /**
     * This method will try to load and decrypt the given token using a JWKSet. If succeeded, the methods will populate
     * the $recipient variable and returns the JWE.
     */
    public function load_and_decrypt_with_key_set(string $token, Jwk_Set $keyset, ?int &$recipient): JWE
    {
        try {
            $jwe = $this->serializer_manager->unserialize($token);
            $nb_recipients = $jwe->count_recipients();
            for ($i = 0; $i < $nb_recipients; ++$i) {
                if ($this->process_recipient($jwe, $keyset, $i)) {
                    $recipient = $i;
                    return $jwe;
                }
            }
        } catch (Throwable) {
            // Nothing to do. Exception thrown just after
        }
        throw new RuntimeException('Unable to load and decrypt the token.');
    }
    private function process_recipient(JWE &$jwe, Jwk_Set $keyset, int $recipient): bool
    {
        try {
            if ($this->header_checker_manager !== null) {
                $this->header_checker_manager->check($jwe, $recipient);
            }
            return $this->jwe_decrypter->decrypt_using_key_set($jwe, $keyset, $recipient);
        } catch (Throwable) {
            return false;
        }
    }
}