<?php

declare (strict_types=1);
use Jose\Component\Key_Management\Jwk_Factory;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Jwk_Factory::class)->public();
};