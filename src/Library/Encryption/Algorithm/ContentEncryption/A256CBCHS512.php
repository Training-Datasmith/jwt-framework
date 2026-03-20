<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Content_Encryption;

use Override;
final readonly class A256CBCHS512 extends AESCBCHS
{
    #[Override]
    public function get_cek_size(): int
    {
        return 512;
    }
    #[Override]
    public function name(): string
    {
        return 'A256CBC-HS512';
    }
    #[Override]
    protected function get_hash_algorithm(): string
    {
        return 'sha512';
    }
    #[Override]
    protected function get_mode(): string
    {
        return 'aes-256-cbc';
    }
}