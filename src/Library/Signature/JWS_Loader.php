<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use Exception;
use Jose\Component\Checker\Header_Checker_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager;
use Throwable;
/**
 * @see \Jose\Tests\Component\Signature\JWSLoaderTest
 */
class Jws_Loader
{
    public function __construct(private readonly Jws_Serializer_Manager $serializer_manager, private readonly Jws_Verifier $jws_verifier, private readonly ?Header_Checker_Manager $header_checker_manager)
    {
    }
    /**
     * Returns the JWSVerifier associated to the JWSLoader.
     */
    public function get_jws_verifier(): Jws_Verifier
    {
        return $this->jws_verifier;
    }
    /**
     * Returns the Header Checker Manager associated to the JWSLoader.
     */
    public function get_header_checker_manager(): ?Header_Checker_Manager
    {
        return $this->header_checker_manager;
    }
    /**
     * Returns the JWSSerializer associated to the JWSLoader.
     */
    public function get_serializer_manager(): Jws_Serializer_Manager
    {
        return $this->serializer_manager;
    }
    /**
     * This method will try to load and verify the token using the given key. It returns a JWS and will populate the
     * $signature variable in case of success, otherwise an exception is thrown.
     */
    public function load_and_verify_with_key(string $token, JWK $key, ?int &$signature, ?string $payload = null): JWS
    {
        $keyset = new Jwk_Set([$key]);
        return $this->load_and_verify_with_key_set($token, $keyset, $signature, $payload);
    }
    /**
     * This method will try to load and verify the token using the given key set. It returns a JWS and will populate the
     * $signature variable in case of success, otherwise an exception is thrown.
     */
    public function load_and_verify_with_key_set(string $token, Jwk_Set $keyset, ?int &$signature, ?string $payload = null): JWS
    {
        try {
            $jws = $this->serializer_manager->unserialize($token);
            $nb_signatures = $jws->count_signatures();
            for ($i = 0; $i < $nb_signatures; ++$i) {
                if ($this->process_signature($jws, $keyset, $i, $payload)) {
                    $signature = $i;
                    return $jws;
                }
            }
        } catch (Throwable) {
            // Nothing to do. Exception thrown just after
        }
        throw new Exception('Unable to load and verify the token.');
    }
    private function process_signature(JWS $jws, Jwk_Set $keyset, int $signature, ?string $payload): bool
    {
        try {
            if ($this->header_checker_manager !== null) {
                $this->header_checker_manager->check($jws, $signature);
            }
            return $this->jws_verifier->verify_with_key_set($jws, $keyset, $signature, $payload);
        } catch (Throwable) {
            return false;
        }
    }
}