<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Symfony\Contracts\Event_Dispatcher\Event;
use Throwable;
final class Claim_Checked_Failure_Event extends Event
{
    public function __construct(private readonly array $claims, private readonly array $mandatory_claims, private readonly Throwable $throwable)
    {
    }
    public function get_claims(): array
    {
        return $this->claims;
    }
    public function get_mandatory_claims(): array
    {
        return $this->mandatory_claims;
    }
    public function get_throwable(): Throwable
    {
        return $this->throwable;
    }
}