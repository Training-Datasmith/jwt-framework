<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use Jose\Component\Core\Algorithm_Manager_Factory;
class Jws_Verifier_Factory
{
    public function __construct(private readonly Algorithm_Manager_Factory $algorithm_manager_factory)
    {
    }
    /**
     * Creates a JWSVerifier using the given signature algorithm aliases.
     *
     * @param string[] $algorithms
     */
    public function create(array $algorithms): Jws_Verifier
    {
        $algorithm_manager = $this->algorithm_manager_factory->create($algorithms);
        return new Jws_Verifier($algorithm_manager);
    }
}