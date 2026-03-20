<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use Jose\Component\Key_Management\Analyzer\Key_Analyzer_Manager;
use Override;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Key_Analyzer_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Key_Analyzer_Manager::class)) {
            return;
        }
        $definition = $container->get_definition(Key_Analyzer_Manager::class);
        $tagged_services = $container->find_tagged_service_ids('jose.key_analyzer');
        foreach ($tagged_services as $id => $tags) {
            $definition->add_method_call('add', [new Reference($id)]);
        }
    }
}