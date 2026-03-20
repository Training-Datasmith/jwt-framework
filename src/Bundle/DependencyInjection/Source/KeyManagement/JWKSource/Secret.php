<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Abstract_Source;
use Jose\Component\Core\JWK;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Secret extends Abstract_Source implements Jwk_Source
{
    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function create_definition(Container_Builder $container, array $config): Definition
    {
        $definition = new Definition(JWK::class);
        $definition->set_factory([new Reference(Jwk_Factory::class), 'createFromSecret']);
        $definition->set_arguments([$config['secret'], $config['additional_values']]);
        $definition->add_tag('jose.jwk');
        return $definition;
    }
    #[Override]
    public function get_key(): string
    {
        return 'secret';
    }
    #[Override]
    public function add_configuration(Node_Definition $node): void
    {
        parent::add_configuration($node);
        $node->children()->scalar_node('secret')->info('The shared secret.')->is_required()->end()->array_node('additional_values')->info('Additional values to be added to the key.')->default_value([])->use_attribute_as_key('key')->variable_prototype()->end()->end()->end();
    }
}