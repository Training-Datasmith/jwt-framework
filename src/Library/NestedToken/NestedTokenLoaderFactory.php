<?php

declare (strict_types=1);
namespace Jose\Component\Nested_Token;

use Jose\Component\Encryption\Jwe_Loader_Factory;
use Jose\Component\Signature\Jws_Loader_Factory;
class Nested_Token_Loader_Factory
{
    public function __construct(private readonly Jwe_Loader_Factory $jwe_loader_factory, private readonly Jws_Loader_Factory $jws_loader_factory)
    {
    }
    /**
     * @param array<string> $jweSerializers
     * @param array<string> $keyEncryptionAlgorithms
     * @param array<string> $jweHeaderCheckers
     * @param array<string> $jwsSerializers
     * @param array<string> $signatureAlgorithms
     * @param array<string> $jwsHeaderCheckers
     */
    public function create(array $jwe_serializers, array $key_encryption_algorithms, array $jwe_header_checkers, array $jws_serializers, array $signature_algorithms, array $jws_header_checkers): Nested_Token_Loader
    {
        $jwe_loader = $this->jwe_loader_factory->create($jwe_serializers, $key_encryption_algorithms, $jwe_header_checkers);
        $jws_loader = $this->jws_loader_factory->create($jws_serializers, $signature_algorithms, $jws_header_checkers);
        return new Nested_Token_Loader($jwe_loader, $jws_loader);
    }
}