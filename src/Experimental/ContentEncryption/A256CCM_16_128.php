<?php

declare (strict_types=1);
namespace Jose\Experimental\Content_Encryption;

use Override;
final readonly class A256CCM_16_128 extends AESCCM
{
    #[Override]
    public function get_cek_size(): int
    {
        return 256;
    }
    #[Override]
    public function name(): string
    {
        return 'A256CCM-16-128';
    }
    #[Override]
    public function get_iv_size(): int
    {
        return 13 * 8;
    }
    #[Override]
    protected function get_mode(): string
    {
        return 'aes-256-ccm';
    }
    #[Override]
    protected function get_tag_length(): int
    {
        return 8;
    }
}