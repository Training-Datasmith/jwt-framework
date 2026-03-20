<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use Jose\Bundle\Jose_Framework\Serializer\Jwe_Encoder;
use Jose\Bundle\Jose_Framework\Serializer\Jwe_Serializer;
use Jose\Bundle\Jose_Framework\Serializer\Jws_Encoder;
use Jose\Bundle\Jose_Framework\Serializer\Jws_Serializer;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager_Factory;
use Override;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Serializer\Serializer;
final readonly class Symfony_Serializer_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!class_exists(Serializer::class)) {
            return;
        }
        if ($container->has_definition(Jws_Serializer_Manager_Factory::class)) {
            $container->autowire(Jws_Serializer::class, Jws_Serializer::class)->set_public(false)->add_tag('serializer.normalizer');
            $container->autowire(Jws_Encoder::class, Jws_Encoder::class)->set_public(false)->add_tag('serializer.encoder');
        }
        if ($container->has_definition(Jwe_Serializer_Manager_Factory::class)) {
            $container->autowire(Jwe_Serializer::class, Jwe_Serializer::class)->set_public(false)->add_tag('serializer.normalizer');
            $container->autowire(Jwe_Encoder::class, Jwe_Encoder::class)->set_public(false)->add_tag('serializer.encoder');
        }
    }
}