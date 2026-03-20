<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use InvalidArgumentException;
use Jose\Bundle\Jose_Framework\Routing\Jwk_Set_Loader;
use Override;
use function sprintf;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
final readonly class Key_Set_Controller_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Jwk_Set_Loader::class)) {
            return;
        }
        $definition = $container->get_definition(Jwk_Set_Loader::class);
        $tagged_algorithm_services = $container->find_tagged_service_ids('jose.jwk_uri.controller');
        foreach ($tagged_algorithm_services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['path'])) {
                    throw new InvalidArgumentException(sprintf('The controller "%s" does not have any "path" attribute.', $id));
                }
                $definition->add_method_call('add', [$attributes['path'], $id]);
            }
        }
    }
}