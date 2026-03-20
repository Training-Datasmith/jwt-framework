<?php

declare (strict_types=1);
use Jose\Component\Encryption\Algorithm\Key_Encryption\Chacha20Poly1305;
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
    $container->set(Chacha20Poly1305::class)->tag('jose.algorithm', ['alias' => 'chacha20-poly1305']);
};