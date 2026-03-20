<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Checker;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Jose\Bundle\Jose_Framework\Services\Header_Checker_Manager;
use Jose\Bundle\Jose_Framework\Services\Header_Checker_Manager_Factory;
use Override;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Header_Checker implements Source
{
    #[Override]
    public function name(): string
    {
        return 'headers';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        foreach ($configs[$this->name()] as $name => $item_config) {
            $service_id = sprintf('jose.header_checker.%s', $name);
            $definition = new Definition(Header_Checker_Manager::class);
            $definition->set_factory([new Reference(Header_Checker_Manager_Factory::class), 'create'])->set_arguments([$item_config['headers']])->add_tag('jose.header_checker_manager')->set_public($item_config['is_public']);
            foreach ($item_config['tags'] as $id => $attributes) {
                $definition->add_tag($id, $attributes);
            }
            $container->set_definition($service_id, $definition);
            $container->register_alias_for_argument($service_id, Header_Checker_Manager::class, $name . 'HeaderCheckerManager');
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $node->children()->array_node($this->name())->treat_false_like([])->treat_null_like([])->use_attribute_as_key('name')->array_prototype()->children()->boolean_node('is_public')->info('If true, the service will be public, else private.')->default_true()->end()->array_node('headers')->info('A list of header aliases to be set in the claim checker.')->use_attribute_as_key('name')->is_required()->scalar_prototype()->end()->end()->array_node('tags')->info('A list of tags to be associated to the claim checker.')->use_attribute_as_key('name')->treat_null_like([])->treat_false_like([])->variable_prototype()->end()->end()->end()->end()->end()->end();
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        return [];
    }
}