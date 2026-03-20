<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Component\Core\Algorithm_Manager_Factory;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final readonly class Jws_Builder_Factory
{
    public function __construct(private readonly Algorithm_Manager_Factory $signature_algorithm_manager_factory, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    /**
     * This method creates a JWSBuilder using the given algorithm aliases.
     *
     * @param string[] $algorithms
     */
    public function create(array $algorithms): Jws_Builder
    {
        $algorithm_manager = $this->signature_algorithm_manager_factory->create($algorithms);
        return new Jws_Builder($algorithm_manager, $this->event_dispatcher);
    }
}