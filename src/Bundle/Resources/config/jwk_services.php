<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Controller\Jwk_Set_Controller_Factory;
use Jose\Bundle\Jose_Framework\Routing\Jwk_Set_Loader;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Jwk_Set_Controller_Factory::class);
    $container->set(Jwk_Set_Loader::class)->tag('routing.loader');
};