<?php

declare (strict_types=1);
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(ES256::class)->tag('jose.algorithm', ['alias' => 'ES256']);
    $container->set(ES384::class)->tag('jose.algorithm', ['alias' => 'ES384']);
    $container->set(ES512::class)->tag('jose.algorithm', ['alias' => 'ES512']);
};