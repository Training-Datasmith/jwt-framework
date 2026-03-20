<?php

declare (strict_types=1);
use Jose\Component\Core\Util\Ecc\Nist_Curve;
use Jose\Component\Key_Management\Analyzer\Algorithm_Analyzer;
use Jose\Component\Key_Management\Analyzer\Es256key_Analyzer;
use Jose\Component\Key_Management\Analyzer\Es384key_Analyzer;
use Jose\Component\Key_Management\Analyzer\Es512key_Analyzer;
use Jose\Component\Key_Management\Analyzer\Hs256key_Analyzer;
use Jose\Component\Key_Management\Analyzer\Hs384key_Analyzer;
use Jose\Component\Key_Management\Analyzer\Hs512key_Analyzer;
use Jose\Component\Key_Management\Analyzer\Key_Analyzer_Manager;
use Jose\Component\Key_Management\Analyzer\Key_Identifier_Analyzer;
use Jose\Component\Key_Management\Analyzer\Keyset_Analyzer_Manager;
use Jose\Component\Key_Management\Analyzer\Mixed_Key_Types;
use Jose\Component\Key_Management\Analyzer\Mixed_Public_And_Private_Keys;
use Jose\Component\Key_Management\Analyzer\None_Analyzer;
use Jose\Component\Key_Management\Analyzer\Oct_Analyzer;
use Jose\Component\Key_Management\Analyzer\Usage_Analyzer;
use Jose\Component\Key_Management\Analyzer\Zxcvbn_Key_Analyzer;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return function (Container_Configurator $container): void {
    $container = $container->services()->defaults()->private()->autoconfigure()->autowire();
    $container->set(Key_Analyzer_Manager::class)->public();
    $container->set(Keyset_Analyzer_Manager::class)->public();
    $container->set(Algorithm_Analyzer::class);
    $container->set(Usage_Analyzer::class);
    $container->set(Key_Identifier_Analyzer::class);
    $container->set(None_Analyzer::class);
    $container->set(Oct_Analyzer::class);
    $container->set(Mixed_Key_Types::class);
    $container->set(Mixed_Public_And_Private_Keys::class);
    $container->set(Hs256key_Analyzer::class);
    $container->set(Hs384key_Analyzer::class);
    $container->set(Hs512key_Analyzer::class);
    if (class_exists(Nist_Curve::class)) {
        $container->set(Es256key_Analyzer::class);
        $container->set(Es384key_Analyzer::class);
        $container->set(Es512key_Analyzer::class);
    }
    $container->set(Zxcvbn_Key_Analyzer::class);
};