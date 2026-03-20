<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\A128KW as Wrapper;
use Override;
final readonly class ECDHESA128KW extends ECDHESAESKW
{
    #[Override]
    public function name(): string
    {
        return 'ECDH-ES+A128KW';
    }
    #[Override]
    protected function get_wrapper(): Wrapper
    {
        return new Wrapper();
    }
    #[Override]
    protected function get_key_length(): int
    {
        return 128;
    }
}