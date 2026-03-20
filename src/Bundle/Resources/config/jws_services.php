<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Services\Jws_Builder_Factory;
use Jose\Bundle\Jose_Framework\Services\Jws_Loader_Factory;
use Jose\Bundle\Jose_Framework\Services\Jws_Verifier_Factory;
use Jose\Component\Signature\Jws_Token_Support;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Jws_Builder_Factory::class)->public();
    $container->set(Jws_Verifier_Factory::class)->public();
    $container->set(Jws_Loader_Factory::class)->public();
    $container->set(Jws_Token_Support::class);
};