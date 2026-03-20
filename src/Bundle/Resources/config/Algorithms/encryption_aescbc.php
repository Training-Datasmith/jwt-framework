<?php

declare (strict_types=1);
use Jose\Component\Encryption\Algorithm\Content_Encryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\Content_Encryption\A192CBCHS384;
use Jose\Component\Encryption\Algorithm\Content_Encryption\A256CBCHS512;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(A128CBCHS256::class)->tag('jose.algorithm', ['alias' => 'A128CBC-HS256']);
    $container->set(A192CBCHS384::class)->tag('jose.algorithm', ['alias' => 'A192CBC-HS384']);
    $container->set(A256CBCHS512::class)->tag('jose.algorithm', ['alias' => 'A256CBC-HS512']);
};