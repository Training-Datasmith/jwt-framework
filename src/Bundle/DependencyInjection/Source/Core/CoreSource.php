<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Core;

use Jose\Bundle\Jose_Framework\Data_Collector\Collector;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Algorithm_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Checker_Collector_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Data_Collector_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Jwe_Collector_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Jws_Collector_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Key_Collector_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source_With_Compiler_Passes;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Env_Var_Processor_Interface;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final readonly class Core_Source implements Source_With_Compiler_Passes
{
    #[Override]
    public function name(): string
    {
        return 'core';
    }
    #[Override]
    public function load(array $config, Container_Builder $container): void
    {
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config'));
        $loader->load('services.php');
        if (interface_exists(Env_Var_Processor_Interface::class)) {
            $loader->load('env_var.php');
        }
        if ($container->get_parameter('kernel.debug') === true) {
            $container->register_for_autoconfiguration(Collector::class)->add_tag('jose.data_collector');
            $loader->load('dev_services.php');
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        // No configuration needed
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        return [];
    }
    /**
     * @return CompilerPassInterface[]
     */
    #[Override]
    public function get_compiler_passes(): array
    {
        return [new Algorithm_Compiler_Pass(), new Data_Collector_Compiler_Pass(), new Checker_Collector_Compiler_Pass(), new Key_Collector_Compiler_Pass(), new Jws_Collector_Compiler_Pass(), new Jwe_Collector_Compiler_Pass()];
    }
}