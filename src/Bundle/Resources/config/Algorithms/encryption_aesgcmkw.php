<?php

declare (strict_types=1);
use AESKW\Wrapper;
use Jose\Component\Encryption\Algorithm\Key_Encryption\A128GCMKW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\A192GCMKW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\A256GCMKW;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    if (interface_exists(Wrapper::class)) {
        $container->set(A128GCMKW::class)->tag('jose.algorithm', ['alias' => 'A128GCMKW']);
        $container->set(A192GCMKW::class)->tag('jose.algorithm', ['alias' => 'A192GCMKW']);
        $container->set(A256GCMKW::class)->tag('jose.algorithm', ['alias' => 'A256GCMKW']);
    }
};