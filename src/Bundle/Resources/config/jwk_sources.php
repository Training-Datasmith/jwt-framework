<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\Certificate_File;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\JWK;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\Jwk_Set;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\Key_File;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\P12;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\Secret;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\Values;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\X5C;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->public()->autoconfigure()->autowire();
    $container->set(Key_File::class);
    $container->set(P12::class);
    $container->set(Certificate_File::class);
    $container->set(Values::class);
    $container->set(Secret::class);
    $container->set(JWK::class);
    $container->set(X5C::class);
    $container->set(Jwk_Set::class);
};