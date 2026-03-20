<?php

declare (strict_types=1);
use Jose\Component\Console\Jku_Loader_Command;
use Jose\Component\Console\X5u_Loader_Command;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Jku_Loader_Command::class);
    $container->set(X5u_Loader_Command::class);
};