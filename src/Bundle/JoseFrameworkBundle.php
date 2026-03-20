<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework;

use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Event_Dispatcher_Alias_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Compiler\Symfony_Serializer_Compiler_Pass;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Jose_Framework_Extension;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Checker\Checker_Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Console\Console_Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Core\Core_Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Encryption\Encryption_Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Key_Management\Key_Management_Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Nested_Token\Nested_Token;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Signature\Signature_Source;
use Jose\Bundle\Jose_Framework\Dependency_Injection\Source\Source_With_Compiler_Passes;
use Override;
use Symfony\Component\Dependency_Injection\Compiler\Pass_Config;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Extension\Extension_Interface;
use Symfony\Component\Http_Kernel\Bundle\Bundle;
final class Jose_Framework_Bundle extends Bundle
{
    /**
     * @var Source\Source[]
     */
    private array $sources = [];
    public function __construct()
    {
        foreach ($this->get_sources() as $source) {
            $this->sources[$source->name()] = $source;
        }
    }
    #[Override]
    public function get_container_extension(): Extension_Interface
    {
        return new Jose_Framework_Extension('jose', $this->sources);
    }
    #[Override]
    public function build(Container_Builder $container): void
    {
        parent::build($container);
        foreach ($this->sources as $source) {
            if ($source instanceof Source_With_Compiler_Passes) {
                $compiler_passes = $source->get_compiler_passes();
                foreach ($compiler_passes as $compiler_pass) {
                    $container->add_compiler_pass($compiler_pass, Pass_Config::TYPE_BEFORE_OPTIMIZATION, 0);
                }
            }
        }
        $container->add_compiler_pass(new Event_Dispatcher_Alias_Compiler_Pass(), Pass_Config::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->add_compiler_pass(new Symfony_Serializer_Compiler_Pass(), Pass_Config::TYPE_BEFORE_OPTIMIZATION, 10);
    }
    /**
     * @return Source\Source[]
     */
    private function get_sources(): iterable
    {
        return [new Core_Source(), new Checker_Source(), new Console_Source(), new Signature_Source(), new Encryption_Source(), new Nested_Token(), new Key_Management_Source()];
    }
}