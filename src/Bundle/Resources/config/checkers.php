<?php

declare (strict_types=1);
use Jose\Bundle\Jose_Framework\Services\Claim_Checker_Manager_Factory;
use Jose\Bundle\Jose_Framework\Services\Header_Checker_Manager_Factory;
use Jose\Component\Checker\Expiration_Time_Checker;
use Jose\Component\Checker\Issued_At_Checker;
use Jose\Component\Checker\Not_Before_Checker;
use Psr\Clock\Clock_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Header_Checker_Manager_Factory::class)->public();
    $container->set(Claim_Checker_Manager_Factory::class)->public();
    $container->set(Expiration_Time_Checker::class)->arg('$clock', service(Clock_Interface::class))->tag('jose.checker.claim', ['alias' => 'exp'])->tag('jose.checker.header', ['alias' => 'exp']);
    $container->set(Issued_At_Checker::class)->arg('$clock', service(Clock_Interface::class))->tag('jose.checker.claim', ['alias' => 'iat'])->tag('jose.checker.header', ['alias' => 'iat']);
    $container->set(Not_Before_Checker::class)->arg('$clock', service(Clock_Interface::class))->tag('jose.checker.claim', ['alias' => 'nbf'])->tag('jose.checker.header', ['alias' => 'nbf']);
};