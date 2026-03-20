<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final readonly class Nested_Token_Loader_Factory
{
    public function __construct(private readonly Jwe_Loader_Factory $jwe_loader_factory, private readonly Jws_Loader_Factory $jws_loader_factory, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function create(array $jwe_serializers, array $encryption_algorithms, array $jwe_header_checkers, array $jws_serializers, array $signature_algorithms, array $jws_header_checkers): Nested_Token_Loader
    {
        $jwe_loader = $this->jwe_loader_factory->create($jwe_serializers, $encryption_algorithms, $jwe_header_checkers);
        $jws_loader = $this->jws_loader_factory->create($jws_serializers, $signature_algorithms, $jws_header_checkers);
        return new Nested_Token_Loader($jwe_loader, $jws_loader, $this->event_dispatcher);
    }
}