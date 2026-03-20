<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use Jose\Bundle\Jose_Framework\Data_Collector\Jose_Collector;
use Override;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Data_Collector_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Jose_Collector::class)) {
            return;
        }
        $definition = $container->get_definition(Jose_Collector::class);
        $tagged_algorithm_services = $container->find_tagged_service_ids('jose.data_collector');
        foreach ($tagged_algorithm_services as $id => $tags) {
            $definition->add_method_call('add', [new Reference($id)]);
        }
    }
}