<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use InvalidArgumentException;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Key_Checker;
use Jose\Component\Signature\Algorithm\Mac_Algorithm;
use Jose\Component\Signature\Algorithm\Signature_Algorithm;
use function sprintf;
use Throwable;
class Jws_Verifier
{
    public function __construct(private readonly Algorithm_Manager $signature_algorithm_manager)
    {
    }
    /**
     * Returns the algorithm manager associated to the JWSVerifier.
     */
    public function get_signature_algorithm_manager(): Algorithm_Manager
    {
        return $this->signature_algorithm_manager;
    }
    /**
     * This method will try to verify the JWS object using the given key and for the given signature. It returns true if
     * the signature is verified, otherwise false.
     *
     * @return bool true if the verification of the signature succeeded, else false
     */
    public function verify_with_key(JWS $jws, JWK $jwk, int $signature, ?string $detached_payload = null): bool
    {
        $jwkset = new Jwk_Set([$jwk]);
        return $this->verify_with_key_set($jws, $jwkset, $signature, $detached_payload);
    }
    /**
     * This method will try to verify the JWS object using the given key set and for the given signature. It returns
     * true if the signature is verified, otherwise false.
     *
     * @param JWS $jws A JWS object
     * @param JWKSet $jwkset The signature will be verified using keys in the key set
     * @param JWK $jwk The key used to verify the signature in case of success
     * @param string|null $detachedPayload If not null, the value must be the detached payload encoded in Base64 URL safe. If the input contains a payload, throws an exception.
     *
     * @return bool true if the verification of the signature succeeded, else false
     */
    public function verify_with_key_set(JWS $jws, Jwk_Set $jwkset, int $signature_index, ?string $detached_payload = null, ?JWK &$jwk = null): bool
    {
        if ($jwkset->count() === 0) {
            throw new InvalidArgumentException('There is no key in the key set.');
        }
        if ($jws->count_signatures() === 0) {
            throw new InvalidArgumentException('The JWS does not contain any signature.');
        }
        $this->check_payload($jws, $detached_payload);
        $signature = $jws->get_signature($signature_index);
        return $this->verify_signature($jws, $jwkset, $signature, $detached_payload, $jwk);
    }
    private function verify_signature(JWS $jws, Jwk_Set $jwkset, Signature $signature, ?string $detached_payload = null, ?JWK &$success_jwk = null): bool
    {
        $input = $this->get_input_to_verify($jws, $signature, $detached_payload);
        $algorithm = $this->get_algorithm($signature);
        foreach ($jwkset->all() as $jwk) {
            try {
                Key_Checker::check_key_usage($jwk, 'verification');
                Key_Checker::check_key_algorithm($jwk, $algorithm->name());
                if ($algorithm->verify($jwk, $input, $signature->get_signature()) === true) {
                    $success_jwk = $jwk;
                    return true;
                }
            } catch (Throwable) {
                //We do nothing, we continue with other keys
                continue;
            }
        }
        return false;
    }
    private function get_input_to_verify(JWS $jws, Signature $signature, ?string $detached_payload): string
    {
        $payload = $jws->get_payload();
        $is_payload_empty = $payload === null || $payload === '';
        $encoded_protected_header = $signature->get_encoded_protected_header() ?? '';
        $is_payload_base64encoded = !$signature->has_protected_header_parameter('b64') || $signature->get_protected_header_parameter('b64') === true;
        $encoded_payload = $jws->get_encoded_payload();
        if ($is_payload_base64encoded && $encoded_payload !== null) {
            return sprintf('%s.%s', $encoded_protected_header, $encoded_payload);
        }
        $callable = $is_payload_base64encoded === true ? static fn(?string $p): string => Base64url_Safe::encode_unpadded($p ?? '') : static fn(?string $p): string => $p ?? '';
        $payload_to_use = $callable($is_payload_empty ? $detached_payload : $payload);
        return sprintf('%s.%s', $encoded_protected_header, $payload_to_use);
    }
    private function check_payload(JWS $jws, ?string $detached_payload = null): void
    {
        $is_payload_empty = $this->is_payload_empty($jws->get_payload());
        if ($detached_payload !== null && !$is_payload_empty) {
            throw new InvalidArgumentException('A detached payload is set, but the JWS already has a payload.');
        }
        if ($is_payload_empty && $detached_payload === null) {
            throw new InvalidArgumentException('The JWS has a detached payload, but no payload is provided.');
        }
    }
    /**
     * @return MacAlgorithm|SignatureAlgorithm
     */
    private function get_algorithm(Signature $signature): Algorithm
    {
        $complete_header = [...$signature->get_protected_header(), ...$signature->get_header()];
        if (!isset($complete_header['alg'])) {
            throw new InvalidArgumentException('No "alg" parameter set in the header.');
        }
        $algorithm = $this->signature_algorithm_manager->get($complete_header['alg']);
        if (!$algorithm instanceof Signature_Algorithm && !$algorithm instanceof Mac_Algorithm) {
            throw new InvalidArgumentException(sprintf('The algorithm "%s" is not supported or is not a signature or MAC algorithm.', $complete_header['alg']));
        }
        return $algorithm;
    }
    private function is_payload_empty(?string $payload): bool
    {
        return $payload === null || $payload === '';
    }
}