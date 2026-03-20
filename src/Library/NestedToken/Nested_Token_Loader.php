<?php

declare (strict_types=1);
namespace Jose\Component\Nested_Token;

use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Jwe_Loader;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Jws_Loader;
class Nested_Token_Loader
{
    public function __construct(private readonly Jwe_Loader $jwe_loader, private readonly Jws_Loader $jws_loader)
    {
    }
    /**
     * This method will try to load, decrypt and verify the token. In case of failure, an exception is thrown, otherwise
     * returns the JWS and populates the $signature variable.
     */
    public function load(string $token, Jwk_Set $encryption_key_set, Jwk_Set $signature_key_set, ?int &$signature = null): JWS
    {
        $recipient = null;
        $jwe = $this->jwe_loader->load_and_decrypt_with_key_set($token, $encryption_key_set, $recipient);
        $this->check_content_type_header($jwe, $recipient);
        if ($jwe->get_payload() === null) {
            throw new InvalidArgumentException('The token has no payload.');
        }
        return $this->jws_loader->load_and_verify_with_key_set($jwe->get_payload(), $signature_key_set, $signature);
    }
    private function check_content_type_header(JWE $jwe, int $recipient): void
    {
        $cty = match (true) {
            $jwe->has_shared_protected_header_parameter('cty') => $jwe->get_shared_protected_header_parameter('cty'),
            $jwe->has_shared_header_parameter('cty') => $jwe->get_shared_header_parameter('cty'),
            $jwe->get_recipient($recipient)->has_header_parameter('cty') => $jwe->get_recipient($recipient)->get_header_parameter('cty'),
            default => throw new InvalidArgumentException('The token is not a nested token.'),
        };
        if (!is_string($cty)) {
            throw new InvalidArgumentException('Invalid "cty" header parameter.');
        }
        if (strcasecmp($cty, 'jwt') !== 0) {
            throw new InvalidArgumentException('The token is not a nested token.');
        }
    }
}