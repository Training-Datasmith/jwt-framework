<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Env_Var_Processor\Key_Env_Var_Processor;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Key_Env_Var_Processor::class);
};