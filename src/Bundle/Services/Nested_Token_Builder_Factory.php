<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager_Factory;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final readonly class Nested_Token_Builder_Factory
{
    public function __construct(private Jwe_Builder_Factory $jwe_builder_factory, private Jwe_Serializer_Manager_Factory $jwe_serializer_manager_factory, private Jws_Builder_Factory $jws_builder_factory, private Jws_Serializer_Manager_Factory $jws_serializer_manager_factory, private Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    public function create(array $jwe_serializers, array $encryption_algorithms, array $jws_serializers, array $signature_algorithms): Nested_Token_Builder
    {
        $jwe_builder = $this->jwe_builder_factory->create($encryption_algorithms);
        $jwe_serializer_manager = $this->jwe_serializer_manager_factory->create($jwe_serializers);
        $jws_builder = $this->jws_builder_factory->create($signature_algorithms);
        $jws_serializer_manager = $this->jws_serializer_manager_factory->create($jws_serializers);
        return new Nested_Token_Builder($jwe_builder, $jwe_serializer_manager, $jws_builder, $jws_serializer_manager, $this->event_dispatcher);
    }
}