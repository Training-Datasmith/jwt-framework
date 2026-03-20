<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Bundle\Jose_Framework\Event\Nested_Token_Issued_Event;
use Jose\Component\Encryption\Jwe_Builder;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager;
use Jose\Component\Nested_Token\Nested_Token_Builder as BaseNestedTokenBuilder;
use Jose\Component\Signature\Jws_Builder;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager;
use Override;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final class Nested_Token_Builder extends Base_Nested_Token_Builder
{
    public function __construct(Jwe_Builder $jwe_builder, Jwe_Serializer_Manager $jwe_serializer_manager, Jws_Builder $jws_builder, Jws_Serializer_Manager $jws_serializer_manager, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
        parent::__construct($jwe_builder, $jwe_serializer_manager, $jws_builder, $jws_serializer_manager);
    }
    #[Override]
    public function create(string $payload, array $signatures, string $jws_serialization_mode, array $jwe_shared_protected_header, array $jwe_shared_header, array $recipients, string $jwe_serialization_mode, ?string $aad = null): string
    {
        $nested_token = parent::create($payload, $signatures, $jws_serialization_mode, $jwe_shared_protected_header, $jwe_shared_header, $recipients, $jwe_serialization_mode, $aad);
        $this->event_dispatcher->dispatch(new Nested_Token_Issued_Event($nested_token));
        return $nested_token;
    }
}