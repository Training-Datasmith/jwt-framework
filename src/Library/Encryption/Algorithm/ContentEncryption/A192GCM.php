<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Content_Encryption;

use Override;
final readonly class A192GCM extends AESGCM
{
    #[Override]
    public function get_cek_size(): int
    {
        return 192;
    }
    #[Override]
    public function name(): string
    {
        return 'A192GCM';
    }
    #[Override]
    protected function get_mode(): string
    {
        return 'aes-192-gcm';
    }
}