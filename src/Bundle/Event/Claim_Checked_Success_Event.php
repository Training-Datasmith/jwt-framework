<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Event;

use Symfony\Contracts\Event_Dispatcher\Event;
final class Claim_Checked_Success_Event extends Event
{
    public function __construct(private readonly array $claims, private readonly array $mandatory_claims, private readonly array $checked_claims)
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
    public function get_checked_claims(): array
    {
        return $this->checked_claims;
    }
}