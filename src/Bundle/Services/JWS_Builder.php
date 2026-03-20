<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Jws_Built_Failure_Event;
use Jose\Bundle\Jose_Framework\Event\Jws_Built_Success_Event;
use Jose\Component\Core\Algorithm_Manager;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Jws_Builder as BaseJWSBuilder;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
use Throwable;
final class Jws_Builder extends Base_Jws_Builder
{
    public function __construct(Algorithm_Manager $signature_algorithm_manager, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($signature_algorithm_manager);
    }
    #[Override]
    public function build(): JWS
    {
        try {
            $jws = parent::build();
            $this->event_dispatcher->dispatch(new Jws_Built_Success_Event($jws));
            return $jws;
        } catch (Throwable $throwable) {
            $this->event_dispatcher->dispatch(new Jws_Built_Failure_Event($this->payload, $this->signatures, $this->is_payload_detached, $this->is_payload_encoded, $throwable));
            throw $throwable;
        }
    }
}