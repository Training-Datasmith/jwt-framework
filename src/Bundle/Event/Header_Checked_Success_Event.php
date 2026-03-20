<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Jose\Component\Core\JWT;
use Symfony\Contracts\Event_Dispatcher\Event;
final class Header_Checked_Success_Event extends Event
{
    public function __construct(private readonly JWT $jwt, private readonly int $index, private readonly array $mandatory_header_parameters)
    {
    }
    public function get_jwt(): JWT
    {
        return $this->jwt;
    }
    public function get_index(): int
    {
        return $this->index;
    }
    public function get_mandatory_header_parameters(): array
    {
        return $this->mandatory_header_parameters;
    }
}