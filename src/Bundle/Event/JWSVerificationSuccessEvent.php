<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Signature\JWS;
use Symfony\Contracts\Event_Dispatcher\Event;
final class Jws_Verification_Success_Event extends Event
{
    public function __construct(private readonly JWS $jws, private readonly Jwk_Set $jwk_set, private readonly int $signature, private readonly ?string $detached_payload, private readonly JWK $JWK)
    {
    }
    public function get_jws(): JWS
    {
        return $this->jws;
    }
    public function get_jwk_set(): Jwk_Set
    {
        return $this->jwk_set;
    }
    public function get_jwk(): JWK
    {
        return $this->JWK;
    }
    public function get_signature(): int
    {
        return $this->signature;
    }
    public function get_detached_payload(): ?string
    {
        return $this->detached_payload;
    }
}