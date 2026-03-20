<?php

declare (strict_types=1);
use Symfony\Component\Routing\Loader\Configurator\Routing_Configurator;
return function (Routing_Configurator $routes): void {
    $routes->import('.', 'jwkset')->methods(['GET']);
};