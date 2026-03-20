<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Data_Collector\Algorithm_Collector;
use Jose\Bundle\Jose_Framework\Data_Collector\Checker_Collector;
use Jose\Bundle\Jose_Framework\Data_Collector\Jose_Collector;
use Jose\Bundle\Jose_Framework\Data_Collector\Jwe_Collector;
use Jose\Bundle\Jose_Framework\Data_Collector\Jws_Collector;
use Jose\Bundle\Jose_Framework\Data_Collector\Key_Collector;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Jose_Collector::class)->tag('data_collector', ['id' => 'jose_collector', 'template' => '@JoseFramework/data_collector/template.html.twig']);
    $container->set(Algorithm_Collector::class);
    $container->set(Checker_Collector::class);
    $container->set(Jwe_Collector::class);
    $container->set(Jws_Collector::class);
    $container->set(Key_Collector::class);
};