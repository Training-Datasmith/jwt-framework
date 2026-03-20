<?php

declare (strict_types=1);
namespace Jose\Component\Encryption;

use Jose\Component\Core\Algorithm_Manager_Factory;
class Jwe_Decrypter_Factory
{
    public function __construct(private readonly Algorithm_Manager_Factory $algorithm_manager_factory)
    {
    }
    /**
     * @param string[] $encryptionAlgorithms
     */
    public function create(array $encryption_algorithms): Jwe_Decrypter
    {
        $algorithm_manager = $this->algorithm_manager_factory->create($encryption_algorithms);
        return new Jwe_Decrypter($algorithm_manager);
    }
}