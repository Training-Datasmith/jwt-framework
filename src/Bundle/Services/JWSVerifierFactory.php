<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Component\Core\Algorithm_Manager_Factory;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final readonly class Jws_Verifier_Factory
{
    public function __construct(private readonly Algorithm_Manager_Factory $algorithm_manager_factory, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function create(array $algorithms): Jws_Verifier
    {
        $algorithm_manager = $this->algorithm_manager_factory->create($algorithms);
        return new Jws_Verifier($algorithm_manager, $this->event_dispatcher);
    }
}