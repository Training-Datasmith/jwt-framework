<?php

declare (strict_types=1);
namespace Jose\Component\Signature;

use Jose\Component\Core\Algorithm_Manager_Factory;
class Jws_Builder_Factory
{
    public function __construct(private readonly Algorithm_Manager_Factory $signature_algorithm_manager_factory)
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
        return new Jws_Builder($algorithm_manager);
    }
}