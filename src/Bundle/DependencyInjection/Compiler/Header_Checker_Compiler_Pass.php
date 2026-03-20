<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use InvalidArgumentException;
use Jose\Bundle\Jose_Framework\Services\Header_Checker_Manager_Factory;
use Override;
use function sprintf;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Header_Checker_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Header_Checker_Manager_Factory::class)) {
            return;
        }
        $definition = $container->get_definition(Header_Checker_Manager_Factory::class);
        $this->add_header_checkers($definition, $container);
        $this->add_token_type($definition, $container);
    }
    private function add_header_checkers(Definition $definition, Container_Builder $container): void
    {
        $tagged_header_checker_services = $container->find_tagged_service_ids('jose.checker.header');
        foreach ($tagged_header_checker_services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias'])) {
                    throw new InvalidArgumentException(sprintf('The header checker "%s" does not have any "alias" attribute.', $id));
                }
                $definition->add_method_call('add', [$attributes['alias'], new Reference($id)]);
            }
        }
    }
    private function add_token_type(Definition $definition, Container_Builder $container): void
    {
        $tagged_header_checker_services = $container->find_tagged_service_ids('jose.checker.token_type');
        foreach ($tagged_header_checker_services as $id => $tags) {
            $definition->add_method_call('addTokenTypeSupport', [new Reference($id)]);
        }
    }
}