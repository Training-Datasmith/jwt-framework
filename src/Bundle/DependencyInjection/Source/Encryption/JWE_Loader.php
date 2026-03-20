<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Encryption;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Jose\Bundle\Jose_Framework\Services\Jwe_Loader_Factory;
use Jose\Component\Encryption\Jwe_Loader as JWELoaderService;
use Override;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jwe_Loader implements Source
{
    #[Override]
    public function name(): string
    {
        return 'loaders';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        foreach ($configs[$this->name()] as $name => $item_config) {
            $service_id = sprintf('jose.jwe_loader.%s', $name);
            $definition = new Definition(Jwe_Loader_Service::class);
            $definition->set_factory([new Reference(Jwe_Loader_Factory::class), 'create'])->set_arguments([$item_config['serializers'], $item_config['encryption_algorithms'], $item_config['header_checkers']])->add_tag('jose.jwe_loader')->set_public($item_config['is_public']);
            foreach ($item_config['tags'] as $id => $attributes) {
                $definition->add_tag($id, $attributes);
            }
            $container->set_definition($service_id, $definition);
            $container->register_alias_for_argument($service_id, Jwe_Loader_Service::class, $name . 'JweLoader');
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $node->children()->array_node($this->name())->requires_at_least_one_element()->use_attribute_as_key('name')->array_prototype()->children()->boolean_node('is_public')->info('If true, the service will be public, else private.')->default_true()->end()->array_node('encryption_algorithms')->info('A list of key encryption algorithm aliases.')->use_attribute_as_key('name')->is_required()->scalar_prototype()->end()->end()->array_node('serializers')->info('A list of signature serializer aliases.')->use_attribute_as_key('name')->requires_at_least_one_element()->scalar_prototype()->end()->end()->array_node('header_checkers')->info('A list of header checker aliases.')->use_attribute_as_key('name')->treat_null_like([])->treat_false_like([])->scalar_prototype()->end()->end()->array_node('tags')->info('A list of tags to be associated to the service.')->use_attribute_as_key('name')->treat_null_like([])->treat_false_like([])->variable_prototype()->end()->end()->end()->end()->end()->end();
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        return [];
    }
}