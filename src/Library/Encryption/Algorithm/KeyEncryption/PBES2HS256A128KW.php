<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\A128KW as Wrapper;
use Override;
final readonly class PBES2HS256A128KW extends PBES2AESKW
{
    #[Override]
    public function name(): string
    {
        return 'PBES2-HS256+A128KW';
    }
    #[Override]
    protected function get_wrapper(): Wrapper
    {
        return new Wrapper();
    }
    #[Override]
    protected function get_hash_algorithm(): string
    {
        return 'sha256';
    }
    #[Override]
    protected function get_key_size(): int
    {
        return 16;
    }
}