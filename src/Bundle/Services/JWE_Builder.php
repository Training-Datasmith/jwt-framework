<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Jwe_Built_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jwe_Built_Success_Event;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Jwe_Builder as BaseJWEBuilder;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Throwable;
final class Jwe_Builder extends Base_Jwe_Builder
{
    public function __construct(Algorithm_Manager $algorithm_manager, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($algorithm_manager);
    }
    #[Override]
    public function build(): JWE
    {
        try {
            $jwe = parent::build();
            $this->event_dispatcher->dispatch(new Jwe_Built_Success_Event($jwe));
            return $jwe;
        } catch (Throwable $throwable) {
            $this->event_dispatcher->dispatch(new Jwe_Built_Failure_Event($this->payload, $this->recipients, $this->shared_protected_header, $this->shared_header, $this->aad, $throwable));
            throw $throwable;
        }
    }
}