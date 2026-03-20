<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use Jose\Bundle\Jose_Framework\Data_Collector\Jws_Collector;
use Override;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jws_Collector_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Jws_Collector::class)) {
            return;
        }
        $definition = $container->get_definition(Jws_Collector::class);
        $services = ['addJWSBuilder' => 'jose.jws_builder', 'addJWSVerifier' => 'jose.jws_verifier', 'addJWSLoader' => 'jose.jws_loader'];
        foreach ($services as $method => $tag) {
            $this->collect_services($method, $tag, $definition, $container);
        }
    }
    private function collect_services(string $method, string $tag, Definition $definition, Container_Builder $container): void
    {
        $tagged_jws_services = $container->find_tagged_service_ids($tag);
        foreach ($tagged_jws_services as $id => $tags) {
            $definition->add_method_call($method, [$id, new Reference($id)]);
        }
    }
}