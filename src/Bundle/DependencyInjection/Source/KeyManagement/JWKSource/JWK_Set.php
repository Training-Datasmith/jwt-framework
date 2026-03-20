<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source;

use function is_int;
use function is_string;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Abstract_Source;
use Jose\Component\Core\JWK;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jwk_Set extends Abstract_Source implements Jwk_Source
{
    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function create_definition(Container_Builder $container, array $config): Definition
    {
        $definition = new Definition(JWK::class);
        $definition->set_factory([new Reference(Jwk_Factory::class), 'createFromKeySet']);
        $definition->set_arguments([new Reference($config['key_set']), $config['index']]);
        $definition->add_tag('jose.jwk');
        return $definition;
    }
    #[Override]
    public function get_key(): string
    {
        return 'jwkset';
    }
    #[Override]
    public function add_configuration(Node_Definition $node): void
    {
        parent::add_configuration($node);
        $node->children()->scalar_node('key_set')->info('The key set service.')->is_required()->end()->variable_node('index')->validate()->if_true(fn(mixed $v): bool => !is_int($v) && !is_string($v))->then_invalid('Invalid keyset index.')->end()->info('The index of the key in the key set.')->is_required()->end()->end();
    }
}