<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source;

use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
interface Source
{
    public function name(): string;
    public function load(array $configs, Container_Builder $container): void;
    public function get_node_definition(Node_Definition $node): void;
    public function prepend(Container_Builder $container, array $config): array;
}