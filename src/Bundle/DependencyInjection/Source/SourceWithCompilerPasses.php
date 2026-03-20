<?php

declare (strict_types=1);
namespace Jose\Bundle\Jose_Framework\Dependency_Injection\Source;

use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
interface Source_With_Compiler_Passes extends Source
{
    /**
     * @return CompilerPassInterface[]
     */
    public function get_compiler_passes(): array;
}