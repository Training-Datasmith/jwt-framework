<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\A256KW as Wrapper;
use Override;
final readonly class PBES2HS512A256KW extends PBES2AESKW
{
    #[Override]
    public function name(): string
    {
        return 'PBES2-HS512+A256KW';
    }
    #[Override]
    protected function get_wrapper(): Wrapper
    {
        return new Wrapper();
    }
    #[Override]
    protected function get_hash_algorithm(): string
    {
        return 'sha512';
    }
    #[Override]
    protected function get_key_size(): int
    {
        return 32;
    }
}