<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use Jose\Component\Checker\Header_Checker_Manager_Factory;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
readonly class Jwe_Loader_Factory
{
    public function __construct(private Jwe_Serializer_Manager_Factory $jwe_serializer_manager_factory, private Jwe_Decrypter_Factory $jwe_decrypter_factory, private ?Header_Checker_Manager_Factory $header_checker_manager_factory)
    {
    }
    public function create(array $serializers, array $encryption_algorithms, array $header_checkers = []): Jwe_Loader
    {
        $serializer_manager = $this->jwe_serializer_manager_factory->create($serializers);
        $jwe_decrypter = $this->jwe_decrypter_factory->create($encryption_algorithms);
        if ($this->header_checker_manager_factory !== null) {
            $header_checker_manager = $this->header_checker_manager_factory->create($header_checkers);
        } else {
            $header_checker_manager = null;
        }
        return new Jwe_Loader($serializer_manager, $jwe_decrypter, $header_checker_manager);
    }
}