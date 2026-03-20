<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Signature;

use Jose\Bundle\Jose_Framework\Services\Jws_Verifier_Factory;
use Jose\Component\Signature\Jws_Verifier as JWSVerifierService;
use Override;
use function sprintf;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Jws_Verifier extends Abstract_Signature_Source
{
    #[Override]
    public function name(): string
    {
        return 'verifiers';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        foreach ($configs[$this->name()] as $name => $item_config) {
            $service_id = sprintf('jose.jws_verifier.%s', $name);
            $definition = new Definition(Jws_Verifier_Service::class);
            $definition->set_factory([new Reference(Jws_Verifier_Factory::class), 'create'])->set_arguments([$item_config['signature_algorithms']])->add_tag('jose.jws_verifier')->set_public($item_config['is_public']);
            foreach ($item_config['tags'] as $id => $attributes) {
                $definition->add_tag($id, $attributes);
            }
            $container->set_definition($service_id, $definition);
            $container->register_alias_for_argument($service_id, Jws_Verifier_Service::class, $name . 'JwsVerifier');
        }
    }
}