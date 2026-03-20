<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Jose\Component\Signature\JWS;
use Symfony\Contracts\Event_Dispatcher\Event;
final class Jws_Built_Success_Event extends Event
{
    public function __construct(private readonly JWS $jws)
    {
    }
    public function get_jws(): JWS
    {
        return $this->jws;
    }
}