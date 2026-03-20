<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Encryption;

use Jose\Bundle\Jose_Framework\Services\Jwe_Builder_Factory;
use Jose\Component\Encryption\Jwe_Builder as JWEBuilderService;
use Override;
use function sprintf;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jwe_Builder extends Abstract_Encryption_Source
{
    #[Override]
    public function name(): string
    {
        return 'builders';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        foreach ($configs[$this->name()] as $name => $item_config) {
            $service_id = sprintf('jose.jwe_builder.%s', $name);
            $definition = new Definition(Jwe_Builder_Service::class);
            $definition->set_factory([new Reference(Jwe_Builder_Factory::class), 'create'])->set_arguments([$item_config['encryption_algorithms']])->add_tag('jose.jwe_builder')->set_public($item_config['is_public']);
            foreach ($item_config['tags'] as $id => $attributes) {
                $definition->add_tag($id, $attributes);
            }
            $container->set_definition($service_id, $definition);
            $container->register_alias_for_argument($service_id, Jwe_Builder_Service::class, $name . 'JweBuilder');
        }
    }
}