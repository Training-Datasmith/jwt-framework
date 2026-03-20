<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Set_Source\JKU;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Set_Source\Jwk_Set;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Set_Source\X5U;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->public()->autoconfigure()->autowire();
    $container->set(Jwk_Set::class);
    $container->set(JKU::class);
    $container->set(X5U::class);
};