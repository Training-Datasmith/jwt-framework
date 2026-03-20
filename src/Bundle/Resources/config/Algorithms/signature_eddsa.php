<?php

declare (strict_types=1);
use Jose\Component\Signature\Algorithm\Ed_Dsa;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Ed_Dsa::class)->tag('jose.algorithm', ['alias' => 'EdDSA']);
};