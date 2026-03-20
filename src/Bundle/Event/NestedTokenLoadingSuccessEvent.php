<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Jose\Component\Core\Jwk_Set;
use Jose\Component\Signature\JWS;
use Symfony\Contracts\Event_Dispatcher\Event;
final class Nested_Token_Loading_Success_Event extends Event
{
    public function __construct(private readonly string $token, private readonly JWS $jws, private readonly Jwk_Set $signature_key_set, private readonly Jwk_Set $encryption_key_set, private readonly int $signature)
    {
    }
    public function get_jws(): JWS
    {
        return $this->jws;
    }
    public function get_token(): string
    {
        return $this->token;
    }
    public function get_signature_key_set(): Jwk_Set
    {
        return $this->signature_key_set;
    }
    public function get_encryption_key_set(): Jwk_Set
    {
        return $this->encryption_key_set;
    }
    public function get_signature(): int
    {
        return $this->signature;
    }
}