<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Override;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Configuration_Interface;
final readonly class Configuration implements Configuration_Interface
{
    /**
     * @param Source[] $sources
     */
    public function __construct(private readonly string $alias, private readonly array $sources)
    {
    }
    #[Override]
    public function get_config_tree_builder(): Tree_Builder
    {
        $tree_builder = new Tree_Builder($this->alias);
        $root_node = $tree_builder->get_root_node();
        foreach ($this->sources as $source) {
            $source->get_node_definition($root_node);
        }
        return $tree_builder;
    }
}