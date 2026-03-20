<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Symfony\Contracts\Event_Dispatcher\Event;
use Throwable;
final class Jws_Built_Failure_Event extends Event
{
    public function __construct(protected ?string $payload, protected array $signatures, protected bool $is_payload_detached, protected ?bool $is_payload_encoded, private readonly Throwable $throwable)
    {
    }
    public function get_payload(): ?string
    {
        return $this->payload;
    }
    public function is_payload_detached(): bool
    {
        return $this->is_payload_detached;
    }
    public function get_signatures(): array
    {
        return $this->signatures;
    }
    public function getis_payload_encoded(): ?bool
    {
        return $this->is_payload_encoded;
    }
    public function get_throwable(): Throwable
    {
        return $this->throwable;
    }
}