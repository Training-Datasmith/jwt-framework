<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Component\Core\Algorithm_Manager_Factory;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final readonly class Jwe_Decrypter_Factory
{
    public function __construct(private Algorithm_Manager_Factory $algorithm_manager_factory, private Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function create(array $encryption_algorithms): Jwe_Decrypter
    {
        $algorithm_manager = $this->algorithm_manager_factory->create($encryption_algorithms);
        return new Jwe_Decrypter($algorithm_manager, $this->event_dispatcher);
    }
}