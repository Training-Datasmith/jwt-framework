<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\A192KW as Wrapper;
use AESKW\Wrapper as WrapperInterface;
use Override;
final readonly class A192KW extends AESKW
{
    #[Override]
    public function name(): string
    {
        return 'A192KW';
    }
    #[Override]
    protected function get_wrapper(): Wrapper_Interface
    {
        return new Wrapper();
    }
}