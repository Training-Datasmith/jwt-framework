<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Encryption;

use function array_key_exists;
use function count;
use function in_array;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Encryption_Serializer_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source_With_Compiler_Passes;
use Jose\Component\Encryption\Algorithm\Content_Encryption\AESCBCHS;
use Jose\Component\Encryption\Algorithm\Content_Encryption\AESGCM;
use Jose\Component\Encryption\Algorithm\Key_Encryption\A128CTR;
use Jose\Component\Encryption\Algorithm\Key_Encryption\AESGCMKW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\AESKW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Chacha20Poly1305;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Dir;
use Jose\Component\Encryption\Algorithm\Key_Encryption\ECDHES;
use Jose\Component\Encryption\Algorithm\Key_Encryption\PBES2AESKW;
use Jose\Component\Encryption\Algorithm\Key_Encryption\RSA;
use Jose\Component\Encryption\Serializer\Jwe_Serializer as JWESerializerAlias;
use Override;
use Symfony\Component\Config\Definition\Builder\Node_Definition;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final readonly class Encryption_Source implements Source_With_Compiler_Passes
{
    /**
     * @var Source[]
     */
    private readonly array $sources;
    public function __construct()
    {
        $this->sources = [new Jwe_Builder(), new Jwe_Decrypter(), new Jwe_Serializer(), new Jwe_Loader()];
    }
    #[Override]
    public function name(): string
    {
        return 'jwe';
    }
    #[Override]
    public function load(array $configs, Container_Builder $container): void
    {
        $container->register_for_autoconfiguration(Jwe_Serializer_Alias::class)->add_tag('jose.jwe.serializer');
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config'));
        $loader->load('jwe_services.php');
        $loader->load('jwe_serializers.php');
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../../../Resources/config/Algorithms/'));
        foreach ($this->get_algorithms_files() as $class => $file) {
            if (class_exists($class)) {
                $loader->load($file);
            }
        }
        if (array_key_exists('jwe', $configs)) {
            foreach ($this->sources as $source) {
                $source->load($configs['jwe'], $container);
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
        return [new Encryption_Serializer_Compiler_Pass()];
    }
    private function get_algorithms_files(): array
    {
        $list = [AESCBCHS::class => 'encryption_aescbc.php', AESGCM::class => 'encryption_aesgcm.php', AESGCMKW::class => 'encryption_aesgcmkw.php', AESKW::class => 'encryption_aeskw.php', Dir::class => 'encryption_dir.php', ECDHES::class => 'encryption_ecdhes.php', PBES2AESKW::class => 'encryption_pbes2.php', RSA::class => 'encryption_rsa.php', A128CTR::class => 'encryption_experimental.php'];
        if (in_array('chacha20-poly1305', openssl_get_cipher_methods(), true)) {
            $list[Chacha20Poly1305::class] = 'encryption_experimental_chacha20_poly1305.php';
        }
        return $list;
    }
}