<?php

declare (strict_types=1);
use AESKW\Wrapper;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHES;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHESA128KW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHESA192KW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHESA256KW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHSS;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHSSA128KW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHSSA192KW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHSSA256KW;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(ECDHES::class)->tag('jose.algorithm', ['alias' => 'ECDH-ES']);
    $container->set(ECDHSS::class)->tag('jose.algorithm', ['alias' => 'ECDH-SS']);
    if (interface_exists(Wrapper::class)) {
        $container->set(ECDHESA128KW::class)->tag('jose.algorithm', ['alias' => 'ECDH-ES+A128KW']);
        $container->set(ECDHESA192KW::class)->tag('jose.algorithm', ['alias' => 'ECDH-ES+A192KW']);
        $container->set(ECDHESA256KW::class)->tag('jose.algorithm', ['alias' => 'ECDH-ES+A256KW']);
        $container->set(ECDHSSA128KW::class)->tag('jose.algorithm', ['alias' => 'ECDH-SS+A128KW']);
        $container->set(ECDHSSA192KW::class)->tag('jose.algorithm', ['alias' => 'ECDH-SS+A192KW']);
        $container->set(ECDHSSA256KW::class)->tag('jose.algorithm', ['alias' => 'ECDH-SS+A256KW']);
    }
};