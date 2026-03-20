<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection;

use function count;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Override;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Extension\Extension;
use Symfony\Component\Dependency_Injection\Extension\Prepend_Extension_Interface;
final class Jose_Framework_Extension extends Extension implements Prepend_Extension_Interface
{
    /**
     * @param Source[] $sources
     */
    public function __construct(private readonly string $alias, private readonly array $sources)
    {
    }
    #[Override]
    public function get_alias(): string
    {
        return $this->alias;
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        $processor = new Processor();
        $config = $processor->process_configuration($this->get_configuration($configs, $container), $configs);
        foreach ($this->sources as $source) {
            $source->load($config, $container);
        }
    }
    #[Override]
    public function get_configuration(array $configs, Container_Builder $container): Configuration
    {
        return new Configuration($this->get_alias(), $this->sources);
    }
    #[Override]
    public function prepend(Container_Builder $container): void
    {
        $configs = $container->get_extension_config($this->get_alias());
        $config = $this->process_configuration($this->get_configuration($configs, $container), $configs);
        foreach ($this->sources as $source) {
            $result = $source->prepend($container, $config);
            if (count($result) !== 0) {
                $container->prepend_extension_config($this->get_alias(), $result);
            }
        }
    }
}