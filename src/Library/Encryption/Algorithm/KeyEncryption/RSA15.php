<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Encryption\Algorithm\Key_Encryption\Util\Rsa_Crypt;
use Override;
final readonly class RSA15 extends RSA
{
    #[Override]
    public function name(): string
    {
        return 'RSA1_5';
    }
    #[Override]
    protected function get_encryption_mode(): int
    {
        return Rsa_Crypt::ENCRYPTION_PKCS1;
    }
    #[Override]
    protected function get_hash_algorithm(): ?string
    {
        return null;
    }
}