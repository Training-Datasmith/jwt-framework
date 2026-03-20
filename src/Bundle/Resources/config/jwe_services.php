<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Services\Jwe_Builder_Factory;
use Jose\Bundle\Jose_Framework\Services\Jwe_Decrypter_Factory;
use Jose\Bundle\Jose_Framework\Services\Jwe_Loader_Factory;
use Jose\Component\Encryption\Jwe_Token_Support;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Jwe_Builder_Factory::class)->public();
    $container->set(Jwe_Decrypter_Factory::class)->public();
    $container->set(Jwe_Loader_Factory::class)->public();
    $container->set(Jwe_Token_Support::class);
};