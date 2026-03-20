<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Encryption;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
abstract readonly class Abstract_Encryption_Source implements Source
{
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $node->children()->array_node($this->name())->use_attribute_as_key('name')->array_prototype()->children()->boolean_node('is_public')->info('If true, the service will be public, else private.')->default_true()->end()->array_node('encryption_algorithms')->info('A list of supported key encryption algorithms.')->use_attribute_as_key('name')->is_required()->requires_at_least_one_element()->scalar_prototype()->end()->end()->array_node('tags')->info('A list of tags to be associated to the service.')->use_attribute_as_key('name')->treat_null_like([])->treat_false_like([])->variable_prototype()->end()->end()->end()->end()->end()->end();
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        return [];
    }
}