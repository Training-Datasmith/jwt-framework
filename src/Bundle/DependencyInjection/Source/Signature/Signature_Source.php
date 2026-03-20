<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Signature;

use function array_key_exists;
use function count;
use function extension_loaded;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Signature_Serializer_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source_With_Compiler_Passes;
use Jose\Component\Signature\Algorithm\ECDSA;
use Jose\Component\Signature\Algorithm\Ed_Dsa;
use Jose\Component\Signature\Algorithm\HMAC;
use Jose\Component\Signature\Algorithm\HS1;
use Jose\Component\Signature\Algorithm\None;
use Jose\Component\Signature\Algorithm\RSAPSS;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final readonly class Signature_Source implements Source_With_Compiler_Passes
{
    /**
     * @var Source[]
     */
    private readonly array $sources;
    public function __construct()
    {
        $this->sources = [new Jws_Builder(), new Jws_Verifier(), new Jws_Serializer(), new Jws_Loader()];
    }
    #[Override]
    public function name(): string
    {
        return 'jws';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        $container->register_for_autoconfiguration(\Jose\Component\Signature\Serializer\Jws_Serializer::class)->add_tag('jose.jws.serializer');
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config/'));
        $loader->load('jws_services.php');
        $loader->load('jws_serializers.php');
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config/Algorithms/'));
        foreach ($this->get_algorithms_files() as $class => $file) {
            if (class_exists($class)) {
                $loader->load($file);
            }
        }
        if (array_key_exists('jws', $configs)) {
            foreach ($this->sources as $source) {
                $source->load($configs['jws'], $container);
            }
        }
    }
    #[Override]
    public function get_node_definition(Node_Definition $node): void
    {
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
        return [new Signature_Serializer_Compiler_Pass()];
    }
    private function get_algorithms_files(): array
    {
        $algorithms = [ECDSA::class => 'signature_ecdsa.php', HMAC::class => 'signature_hmac.php', None::class => 'signature_none.php', HS1::class => 'signature_experimental.php', RSAPSS::class => 'signature_rsa.php'];
        if (extension_loaded('sodium')) {
            $algorithms[Ed_Dsa::class] = 'signature_eddsa.php';
        }
        return $algorithms;
    }
}