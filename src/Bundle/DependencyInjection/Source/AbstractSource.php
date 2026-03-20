<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source;

use function sprintf;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
abstract readonly class Abstract_Source
{
    /**
     * @param array{is_public: bool, tags: array<string, array>, string?: mixed} $config
     */
    public function create(Container_Builder $container, string $type, string $name, array $config): void
    {
        $service_id = sprintf('jose.%s.%s', $type, $name);
        $definition = $this->create_definition($container, $config);
        $definition->set_public($config['is_public']);
        foreach ($config['tags'] as $id => $attributes) {
            $definition->add_tag($id, $attributes);
        }
        $container->set_definition($service_id, $definition);
        $container->register_alias_for_argument($service_id, $definition->get_class() ?? '', $name . ' ' . $type);
    }
    public function add_configuration(Node_Definition $node): void
    {
        $node->children()->boolean_node('is_public')->info('If true, the service will be public, else private.')->default_true()->end()->array_node('tags')->info('A list of tags to be associated to the service.')->use_attribute_as_key('name')->treat_null_like([])->treat_false_like([])->variable_prototype()->end()->end()->end();
    }
    /**
     * @param array<string, mixed> $config
     */
    abstract protected function create_definition(Container_Builder $container, array $config): Definition;
}