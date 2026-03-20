<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Content_Encryption;

use Override;
final readonly class A192CBCHS384 extends AESCBCHS
{
    #[Override]
    public function get_cek_size(): int
    {
        return 384;
    }
    #[Override]
    public function name(): string
    {
        return 'A192CBC-HS384';
    }
    #[Override]
    protected function get_hash_algorithm(): string
    {
        return 'sha384';
    }
    #[Override]
    protected function get_mode(): string
    {
        return 'aes-192-cbc';
    }
}