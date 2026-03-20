<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management;

use function array_key_exists;
use function count;
use InvalidArgumentException;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Jwk_Source\Jwk_Source as JWKSourceInterface;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use LogicException;
use Override;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final class Jwk_Source implements Source
{
    /**
     * @var JWKSourceInterface[]|null
     */
    private ?array $jwk_sources = null;
    #[Override]
    public function name(): string
    {
        return 'keys';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        $sources = $this->get_jwk_sources();
        foreach ($configs[$this->name()] as $name => $item_config) {
            foreach ($item_config as $source_name => $source_config) {
                if (array_key_exists($source_name, $sources)) {
                    $source = $sources[$source_name];
                    $source->create($container, 'key', $name, $source_config);
                } else {
                    throw new LogicException(sprintf('The JWK definition "%s" is not configured.', $name));
                }
            }
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
        $source_node_builder = $node->children()->array_node('keys')->treat_false_like([])->treat_null_like([])->use_attribute_as_key('name')->array_prototype()->validate()->if_true(fn($config): bool => count($config) !== 1)->then_invalid('One key type must be set.')->end()->children();
        foreach ($this->get_jwk_sources() as $name => $source) {
            $source_node = $source_node_builder->array_node($name)->can_be_unset();
            $source->add_configuration($source_node);
        }
    }
    #[Override]
    public function prepend(Container_Builder $container, array $config): array
    {
        return [];
    }
    /**
     * @return JWKSourceInterface[]
     */
    private function get_jwk_sources(): array
    {
        if ($this->jwk_sources !== null) {
            return $this->jwk_sources;
        }
        // load bundled adapter factories
        $temp_container = new Container_Builder();
        $temp_container->register_for_autoconfiguration(Jwk_Source_Interface::class)->add_tag('jose.jwk_source');
        $loader = new Php_File_Loader($temp_container, new File_Locator(__DIR__ . '/../../../Resources/config'));
        $loader->load('jwk_sources.php');
        $temp_container->compile(true);
        $services = $temp_container->find_tagged_service_ids('jose.jwk_source');
        $jwk_sources = [];
        foreach (array_keys($services) as $id) {
            $factory = $temp_container->get($id);
            if (!$factory instanceof Jwk_Source_Interface) {
                throw new InvalidArgumentException('Invalid object');
            }
            $jwk_sources[str_replace('-', '_', $factory->get_key())] = $factory;
        }
        $this->jwk_sources = $jwk_sources;
        return $jwk_sources;
    }
}