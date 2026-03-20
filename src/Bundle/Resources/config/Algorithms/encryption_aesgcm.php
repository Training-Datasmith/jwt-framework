<?php

declare (strict_types=1);
use Jose\Component\Encryption\Algorithm\Content_Encryption\A128GCM;
use Jose\Component\Encryption\Algorithm\Content_Encryption\A192GCM;
use Jose\Component\Encryption\Algorithm\Content_Encryption\A256GCM;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(A128GCM::class)->tag('jose.algorithm', ['alias' => 'A128GCM']);
    $container->set(A192GCM::class)->tag('jose.algorithm', ['alias' => 'A192GCM']);
    $container->set(A256GCM::class)->tag('jose.algorithm', ['alias' => 'A256GCM']);
};