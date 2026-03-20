<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use Jose\Component\Checker\Header_Checker_Manager_Factory;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager_Factory;
class Jws_Loader_Factory
{
    public function __construct(private readonly Jws_Serializer_Manager_Factory $jws_serializer_manager_factory, private readonly Jws_Verifier_Factory $jws_verifier_factory, private readonly ?Header_Checker_Manager_Factory $header_checker_manager_factory)
    {
    }
    /**
     * Creates a JWSLoader using the given serializer aliases, signature algorithm aliases and (optionally) the header
     * checker aliases.
     */
    /**
     * @param array<string> $serializers
     * @param array<string> $algorithms
     * @param array<string> $headerCheckers
     */
    public function create(array $serializers, array $algorithms, array $header_checkers = []): Jws_Loader
    {
        $serializer_manager = $this->jws_serializer_manager_factory->create($serializers);
        $jws_verifier = $this->jws_verifier_factory->create($algorithms);
        if ($this->header_checker_manager_factory !== null) {
            $header_checker_manager = $this->header_checker_manager_factory->create($header_checkers);
        } else {
            $header_checker_manager = null;
        }
        return new Jws_Loader($serializer_manager, $jws_verifier, $header_checker_manager);
    }
}