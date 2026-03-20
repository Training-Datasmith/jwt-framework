<?php

declare (strict_types=1);
use Jose\Component\Signature\Algorithm\Blake2b;
use Jose\Component\Signature\Algorithm\ES256K;
use Jose\Component\Signature\Algorithm\HS1;
use Jose\Component\Signature\Algorithm\HS256_64;
use Jose\Component\Signature\Algorithm\RS1;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
/*
 * ---- New algorithms ----
 * These algorithms are out of the main specifications but referenced in
 * some WebAuthn documents.
 *
 * They may be subject to changes.
 * ------------------------
 */
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(RS1::class)->tag('jose.algorithm', ['alias' => 'RS1']);
    $container->set(HS1::class)->tag('jose.algorithm', ['alias' => 'HS1']);
    $container->set(HS256_64::class)->tag('jose.algorithm', ['alias' => 'HS256/64']);
    $container->set(ES256K::class)->tag('jose.algorithm', ['alias' => 'ES256K']);
    $container->set(Blake2b::class)->tag('jose.algorithm', ['alias' => 'BLAKE2B']);
};