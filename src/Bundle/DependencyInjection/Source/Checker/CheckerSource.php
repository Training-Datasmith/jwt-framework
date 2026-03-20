<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Checker;

use function array_key_exists;
use function count;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Claim_Checker_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Header_Checker_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source_With_Compiler_Passes;
use Jose\Component\Checker\Token_Type_Support;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final readonly class Checker_Source implements Source_With_Compiler_Passes
{
    /**
     * @var Source[]
     */
    private readonly array $sources;
    public function __construct()
    {
        $this->sources = [new Claim_Checker(), new Header_Checker()];
    }
    #[Override]
    public function name(): string
    {
        return 'checkers';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        $container->register_for_autoconfiguration(Token_Type_Support::class)->add_tag('jose.checker.token_type');
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config'));
        $loader->load('checkers.php');
        $container->set_alias('jose.clock', $configs['clock']);
        if (array_key_exists('checkers', $configs)) {
            foreach ($this->sources as $source) {
                $source->load($configs['checkers'], $container);
            }
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $node->children()->scalar_node('clock')->default_value('jose.internal_clock')->cannot_be_empty()->info('PSR-20 clock')->end()->end();
        $child_node = $node->children()->array_node($this->name())->add_defaults_if_not_set()->treat_false_like([])->treat_null_like([]);
        foreach ($this->sources as $source) {
            $source->get_node_definition($child_node);
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
        return [new Claim_Checker_Compiler_Pass(), new Header_Checker_Compiler_Pass()];
    }
}