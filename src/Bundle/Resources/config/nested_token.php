<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Services\Nested_Token_Builder_Factory;
use Jose\Bundle\Jose_Framework\Services\Nested_Token_Loader_Factory;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Nested_Token_Builder_Factory::class)->public();
    $container->set(Nested_Token_Loader_Factory::class)->public();
};