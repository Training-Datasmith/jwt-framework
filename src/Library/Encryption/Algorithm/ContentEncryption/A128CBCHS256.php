<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Content_Encryption;

use Override;
final readonly class A128CBCHS256 extends AESCBCHS
{
    #[Override]
    public function get_cek_size(): int
    {
        return 256;
    }
    #[Override]
    public function name(): string
    {
        return 'A128CBC-HS256';
    }
    #[Override]
    protected function get_hash_algorithm(): string
    {
        return 'sha256';
    }
    #[Override]
    protected function get_mode(): string
    {
        return 'aes-128-cbc';
    }
}