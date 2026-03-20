<?php

declare (strict_types=1);
use Jose\Component\Encryption\Serializer\Compact_Serializer;
use Jose\Component\Encryption\Serializer\Json_Flattened_Serializer;
use Jose\Component\Encryption\Serializer\Json_General_Serializer;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Jwe_Serializer_Manager_Factory::class)->public();
    $container->set(Compact_Serializer::class);
    $container->set(Json_Flattened_Serializer::class);
    $container->set(Json_General_Serializer::class);
};