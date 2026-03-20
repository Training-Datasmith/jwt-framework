<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Set_Source;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Abstract_Source;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Key_Management\X5u_Factory;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class X5U extends Abstract_Source implements Jwk_Set_Source
{
    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function create_definition(Container_Builder $container, array $config): Definition
    {
        $definition = new Definition(Jwk_Set::class);
        $definition->set_factory([new Reference(X5u_Factory::class), 'loadFromUrl']);
        $definition->set_arguments([$config['url'], $config['headers']]);
        $definition->add_tag('jose.jwkset');
        return $definition;
    }
    #[Override]
    public function get_key_set(): string
    {
        return 'x5u';
    }
    #[Override]
    public function add_configuration(Node_Definition $node): void
    {
        parent::add_configuration($node);
        $node->children()->scalar_node('url')->info('URL of the key set.')->is_required()->end()->array_node('headers')->treat_null_like([])->treat_false_like([])->info('Header key/value pairs added to the request.')->use_attribute_as_key('name')->variable_prototype()->end()->end()->end();
    }
}