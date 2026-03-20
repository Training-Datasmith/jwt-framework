<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Services;

use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use Psr\Event_Dispatcher\Event_Dispatcher_Interface;
final readonly class Jwe_Loader_Factory
{
    public function __construct(private readonly Jwe_Serializer_Manager_Factory $jwe_serializer_manager_factory, private readonly Jwe_Decrypter_Factory $jwe_decrypter_factory, private readonly ?Header_Checker_Manager_Factory $header_checker_manager_factory, private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    /**
     * @param array<string> $serializers
     * @param array<string> $encryptionAlgorithms
     * @param array<string> $headerCheckers
     */
    public function create(array $serializers, array $encryption_algorithms, array $header_checkers = []): Jwe_Loader
    {
        $serializer_manager = $this->jwe_serializer_manager_factory->create($serializers);
        $jwe_decrypter = $this->jwe_decrypter_factory->create($encryption_algorithms);
        if ($this->header_checker_manager_factory !== null) {
            $header_checker_manager = $this->header_checker_manager_factory->create($header_checkers);
        } else {
            $header_checker_manager = null;
        }
        return new Jwe_Loader($serializer_manager, $jwe_decrypter, $header_checker_manager, $this->event_dispatcher);
    }
}