<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler;

use InvalidArgumentException;
use Jose\Bundle\Jose_Framework\Services\Claim_Checker_Manager_Factory;
use Override;
use function sprintf;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Reference;
final readonly class Claim_Checker_Compiler_Pass implements Compiler_Pass_Interface
{
    #[Override]
    public function process(Container_Builder $container): void
    {
        if (!$container->has_definition(Claim_Checker_Manager_Factory::class)) {
            return;
        }
        $definition = $container->get_definition(Claim_Checker_Manager_Factory::class);
        $tagged_claim_checker_services = $container->find_tagged_service_ids('jose.checker.claim');
        foreach ($tagged_claim_checker_services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias'])) {
                    throw new InvalidArgumentException(sprintf('The claim checker "%s" does not have any "alias" attribute.', $id));
                }
                $definition->add_method_call('add', [$attributes['alias'], new Reference($id)]);
            }
        }
    }
}