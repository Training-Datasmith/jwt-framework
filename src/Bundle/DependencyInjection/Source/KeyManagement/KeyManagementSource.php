<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management;

use function count;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Key_Analyzer_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Keyset_Analyzer_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Key_Set_Controller_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source_With_Compiler_Passes;
use Jose\Component\Key_Management\Analyzer\Key_Analyzer;
use Jose\Component\Key_Management\Analyzer\Keyset_Analyzer;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final readonly class Key_Management_Source implements Source_With_Compiler_Passes
{
    /**
     * @var Source[]
     */
    private readonly array $sources;
    public function __construct()
    {
        $this->sources = [new Jwk_Set_Source(), new Jwk_Source(), new Jwk_Uri_Source(), new Jku_Source()];
    }
    #[Override]
    public function name(): string
    {
        return 'key_mgmt';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        $container->register_for_autoconfiguration(Key_Analyzer::class)->add_tag('jose.key_analyzer');
        $container->register_for_autoconfiguration(Keyset_Analyzer::class)->add_tag('jose.keyset_analyzer');
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config'));
        $loader->load('analyzers.php');
        $loader->load('jwk_factory.php');
        $loader->load('jwk_services.php');
        foreach ($this->sources as $source) {
            $source->load($configs, $container);
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        foreach ($this->sources as $source) {
            $source->get_node_definition($node);
        }
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        $result = [];
        foreach ($this->sources as $source) {
            $prepend = $source->prepend($container, $config);
            if (count($prepend) !== 0) {
                $result[$source->name()] = $prepend;
            }
        }
        return $result;
    }
    /**
     * @return CompilerPassInterface[]
     */
    #[Override]
    public function get_compiler_passes(): array
    {
        return [new Key_Analyzer_Compiler_Pass(), new Keyset_Analyzer_Compiler_Pass(), new Key_Set_Controller_Compiler_Pass()];
    }
}