<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Symfony\Contracts\Event_Dispatcher\Event;
use Throwable;
final class Jwe_Built_Failure_Event extends Event
{
    public function __construct(private readonly ?string $payload, private readonly array $recipients, private readonly array $shared_protected_header, private readonly array $shared_header, private readonly ?string $aad, private readonly Throwable $throwable)
    {
    }
    public function get_payload(): ?string
    {
        return $this->payload;
    }
    public function get_recipients(): array
    {
        return $this->recipients;
    }
    public function get_shared_protected_header(): array
    {
        return $this->shared_protected_header;
    }
    public function get_shared_header(): array
    {
        return $this->shared_header;
    }
    public function get_aad(): ?string
    {
        return $this->aad;
    }
    public function get_throwable(): Throwable
    {
        return $this->throwable;
    }
}