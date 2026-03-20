<?php

declare (strict_types=1);
use Jose\Component\Key_Management\Jku_Factory;
use Jose\Component\Key_Management\X5u_Factory;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
use Symfony\Contracts\Http_Client\Http_Client_Interface;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    if (interface_exists(Http_Client_Interface::class)) {
        $container->set(Jku_Factory::class)->public()->args([service('jose.http_client')]);
        $container->set(X5u_Factory::class)->public()->args([service('jose.http_client')]);
    }
};