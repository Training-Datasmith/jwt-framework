<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Jose\Component\Core\Jwk_Set;
use Symfony\Contracts\Event_Dispatcher\Event;
use Throwable;
final class Jws_Loading_Failure_Event extends Event
{
    public function __construct(private readonly string $token, private readonly Jwk_Set $jwk_set, private readonly Throwable $throwable)
    {
    }
    public function get_token(): string
    {
        return $this->token;
    }
    public function get_jwk_set(): Jwk_Set
    {
        return $this->jwk_set;
    }
    public function get_throwable(): Throwable
    {
        return $this->throwable;
    }
}