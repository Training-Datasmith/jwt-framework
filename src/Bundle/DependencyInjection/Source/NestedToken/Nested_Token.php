<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Nested_Token;

use function array_key_exists;
use function count;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final readonly class Nested_Token implements Source
{
    /**
     * @var Source[]
     */
    private readonly array $sources;
    public function __construct()
    {
        $this->sources = [new Nested_Token_Loader(), new Nested_Token_Builder()];
    }
    #[Override]
    public function name(): string
    {
        return 'nested_token';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config'));
        $loader->load('nested_token.php');
        if (array_key_exists('nested_token', $configs)) {
            foreach ($this->sources as $source) {
                $source->load($configs['nested_token'], $container);
            }
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $child_node = $node->children()->array_node($this->name())->treat_null_like([])->treat_false_like([]);
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
}