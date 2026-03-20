<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Set_Source;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Abstract_Source;
use Jose\Component\Core\Jwk_Set as JWKSetAlias;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jwk_Set extends Abstract_Source implements Jwk_Set_Source
{
    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function create_definition(Container_Builder $container, array $config): Definition
    {
        $definition = new Definition(Jwk_Set_Alias::class);
        $definition->set_factory([new Reference(Jwk_Factory::class), 'createFromJsonObject']);
        $definition->set_arguments([$config['value']]);
        $definition->add_tag('jose.jwkset');
        return $definition;
    }
    #[Override]
    public function get_key_set(): string
    {
        return 'jwkset';
    }
    #[Override]
    public function add_configuration(Node_Definition $node): void
    {
        parent::add_configuration($node);
        $node->children()->scalar_node('value')->info('The JWKSet object.')->is_required()->end()->end();
    }
}