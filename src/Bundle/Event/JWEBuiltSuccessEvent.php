<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Jose\Component\Encryption\JWE;
use Symfony\Contracts\Event_Dispatcher\Event;
final class Jwe_Built_Success_Event extends Event
{
    public function __construct(private readonly JWE $jwe)
    {
    }
    public function get_jwe(): JWE
    {
        return $this->jwe;
    }
}