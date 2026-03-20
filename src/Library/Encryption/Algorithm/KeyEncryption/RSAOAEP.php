<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Encryption\Algorithm\Key_Encryption\Util\Rsa_Crypt;
use Override;
final readonly class RSAOAEP extends RSA
{
    #[Override]
    public function name(): string
    {
        return 'RSA-OAEP';
    }
    #[Override]
    protected function get_encryption_mode(): int
    {
        return Rsa_Crypt::ENCRYPTION_OAEP;
    }
    #[Override]
    protected function get_hash_algorithm(): string
    {
        return 'sha1';
    }
}