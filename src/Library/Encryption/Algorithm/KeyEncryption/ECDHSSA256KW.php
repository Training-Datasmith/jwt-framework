<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\A256KW as Wrapper;
use Override;
final readonly class ECDHSSA256KW extends ECDHSSAESKW
{
    /**
     * NOTE: the return name was modified
     */
    #[Override]
    public function name(): string
    {
        return 'ECDH-SS+A256KW';
    }
    #[Override]
    protected function get_wrapper(): Wrapper
    {
        return new Wrapper();
    }
    #[Override]
    protected function get_key_length(): int
    {
        return 256;
    }
}