<?php

declare (strict_types=1);
use AESKW\Wrapper;
use Jose\Component\Encryption\Algorithm\Key_Encryption\A128KW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\A192KW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\A256KW;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    if (interface_exists(Wrapper::class)) {
        $container->set(A128KW::class)->tag('jose.algorithm', ['alias' => 'A128KW']);
        $container->set(A192KW::class)->tag('jose.algorithm', ['alias' => 'A192KW']);
        $container->set(A256KW::class)->tag('jose.algorithm', ['alias' => 'A256KW']);
    }
};