<?php

declare (strict_types=1);
use Jose\Component\Encryption\Algorithm\Key_Encryption\RSA15;
use Jose\Component\Encryption\Algorithm\Key_Encryption\RSAOAEP;
use Jose\Component\Encryption\Algorithm\Key_Encryption\RSAOAEP256;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(RSA15::class)->tag('jose.algorithm', ['alias' => 'RSA1_5']);
    $container->set(RSAOAEP::class)->tag('jose.algorithm', ['alias' => 'RSA-OAEP']);
    $container->set(RSAOAEP256::class)->tag('jose.algorithm', ['alias' => 'RSA-OAEP-256']);
};