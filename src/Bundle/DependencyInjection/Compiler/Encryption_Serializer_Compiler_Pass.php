<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager_Factory;
use Override;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Encryption_Serializer_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Jwe_Serializer_Manager_Factory::class)) {
            return;
        }
        $definition = $container->get_definition(Jwe_Serializer_Manager_Factory::class);
        $tagged_algorithm_services = $container->find_tagged_service_ids('jose.jwe.serializer');
        foreach ($tagged_algorithm_services as $id => $tags) {
            $definition->add_method_call('add', [new Reference($id)]);
        }
    }
}