<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Claim_Checked_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Claim_Checked_Success_Event;
use Jose\Component\Checker\Claim_Checker_Manager as BaseClaimCheckerManager;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Throwable;
final class Claim_Checker_Manager extends Base_Claim_Checker_Manager
{
    public function __construct(iterable $checkers, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($checkers);
    }
    #[Override]
    public function check(array $claims, array $mandatory_claims = []): array
    {
        try {
            $checked_claims = Base_Claim_Checker_Manager::check($claims, $mandatory_claims);
            $this->event_dispatcher->dispatch(new Claim_Checked_Success_Event($claims, $mandatory_claims, $checked_claims));
            return $checked_claims;
        } catch (Throwable $throwable) {
            $this->event_dispatcher->dispatch(new Claim_Checked_Failure_Event($claims, $mandatory_claims, $throwable));
            throw $throwable;
        }
    }
}