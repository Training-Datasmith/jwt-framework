<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Override;
final readonly class A128GCMKW extends AESGCMKW
{
    #[Override]
    public function name(): string
    {
        return 'A128GCMKW';
    }
    #[Override]
    protected function get_key_size(): int
    {
        return 128;
    }
}