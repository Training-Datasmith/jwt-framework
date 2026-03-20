<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source;

use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
interface Jwk_Source
{
    /**
     * Creates the JWK, registers it and returns its id.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param string $type The type of the service
     * @param string $id The id of the service
     * @param array<string, mixed> $config An array of configuration
     */
    public function create(Container_Builder $container, string $type, string $id, array $config): void;
    /**
     * Returns the key for the Key Source configuration.
     */
    public function get_key(): string;
    /**
     * Adds configuration nodes for this service.
     */
    public function add_configuration(Node_Definition $builder): void;
}