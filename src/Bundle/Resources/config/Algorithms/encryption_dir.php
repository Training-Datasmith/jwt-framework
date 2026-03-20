<?php

declare (strict_types=1);
use Jose\Component\Encryption\Algorithm\Key_Encryption\Dir;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Dir::class)->tag('jose.algorithm', ['alias' => 'dir']);
};