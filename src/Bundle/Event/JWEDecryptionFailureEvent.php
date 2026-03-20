<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Jose\Component\Core\Jwk_Set;
use Jose\Component\Encryption\JWE;
use Symfony\Contracts\Event_Dispatcher\Event;
final class Jwe_Decryption_Failure_Event extends Event
{
    public function __construct(private readonly JWE $jwe, private readonly Jwk_Set $jwk_set)
    {
    }
    public function get_jwk_set(): Jwk_Set
    {
        return $this->jwk_set;
    }
    public function get_jwe(): JWE
    {
        return $this->jwe;
    }
}