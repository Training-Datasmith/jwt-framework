<?php

declare (strict_types=1);
namespace Jose\Experimental\Content_Encryption;

use Override;
final readonly class A128CCM_16_64 extends AESCCM
{
    #[Override]
    public function get_cek_size(): int
    {
        return 128;
    }
    #[Override]
    public function name(): string
    {
        return 'A128CCM-16-64';
    }
    #[Override]
    public function get_iv_size(): int
    {
        return 13 * 8;
    }
    #[Override]
    protected function get_mode(): string
    {
        return 'aes-128-ccm';
    }
    #[Override]
    protected function get_tag_length(): int
    {
        return 8;
    }
}