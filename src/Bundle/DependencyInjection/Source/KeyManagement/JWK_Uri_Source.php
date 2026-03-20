<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management;

use Jose\Bundle\Jose_Framework\Controller\Jwk_Set_Controller;
use Jose\Bundle\Jose_Framework\Controller\Jwk_Set_Controller_Factory;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Override;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jwk_Uri_Source implements Source
{
    #[Override]
    public function name(): string
    {
        return 'jwk_uris';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        foreach ($configs[$this->name()] as $name => $item_config) {
            $service_id = sprintf('jose.controller.%s', $name);
            $definition = new Definition(Jwk_Set_Controller::class);
            $definition->set_factory([new Reference(Jwk_Set_Controller_Factory::class), 'create']);
            $definition->set_arguments([new Reference($item_config['id'])]);
            $definition->add_tag('jose.jwk_uri.controller', ['path' => $item_config['path']]);
            $definition->add_tag('controller.service_arguments');
            $definition->set_public($item_config['is_public']);
            foreach ($item_config['tags'] as $id => $attributes) {
                $definition->add_tag($id, $attributes);
            }
            $container->set_definition($service_id, $definition);
            $container->register_alias_for_argument($service_id, Jwk_Set_Controller::class, $name . 'JwkSetController');
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $node->children()->array_node('jwk_uris')->treat_false_like([])->treat_null_like([])->use_attribute_as_key('name')->array_prototype()->children()->scalar_node('id')->info('The service ID of the Key Set to share.')->is_required()->end()->scalar_node('path')->info('To share the JWKSet, then set a valid path (e.g. "/jwkset.json").')->is_required()->end()->array_node('tags')->info('A list of tags to be associated to the service.')->use_attribute_as_key('name')->treat_null_like([])->treat_false_like([])->variable_prototype()->end()->end()->boolean_node('is_public')->info('If true, the service will be public, else private.')->default_true()->end()->end()->end()->end()->end();
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        return [];
    }
}