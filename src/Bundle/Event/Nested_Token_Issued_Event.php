<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Symfony\Contracts\Event_Dispatcher\Event;
final class Nested_Token_Issued_Event extends Event
{
    public function __construct(private readonly string $nested_token)
    {
    }
    public function get_nested_token(): string
    {
        return $this->nested_token;
    }
}