<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final readonly class Jku_Source implements Source
{
    #[Override]
    public function name(): string
    {
        return 'jku_factory';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        if ($configs[$this->name()]['enabled'] === true) {
            $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config'));
            $loader->load('jku_source.php');
            $loader->load('jku_commands.php');
            $container->set_alias('jose.http_client', $configs[$this->name()]['client']);
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $node->children()->array_node('jku_factory')->can_be_enabled()->children()->scalar_node('client')->info('HTTP Client used to retrieve key sets.')->is_required()->end()->end()->end()->end();
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        return [];
    }
}