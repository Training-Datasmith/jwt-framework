<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use Jose\Bundle\Jose_Framework\Data_Collector\Jwe_Collector;
use Override;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jwe_Collector_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Jwe_Collector::class)) {
            return;
        }
        $definition = $container->get_definition(Jwe_Collector::class);
        $services = ['addJWEBuilder' => 'jose.jwe_builder', 'addJWEDecrypter' => 'jose.jwe_decrypter', 'addJWELoader' => 'jose.jwe_loader'];
        foreach ($services as $method => $tag) {
            $this->collect_services($method, $tag, $definition, $container);
        }
    }
    private function collect_services(string $method, string $tag, Definition $definition, Container_Builder $container): void
    {
        $tagged_jwe_services = $container->find_tagged_service_ids($tag);
        foreach ($tagged_jwe_services as $id => $tags) {
            $definition->add_method_call($method, [$id, new Reference($id)]);
        }
    }
}