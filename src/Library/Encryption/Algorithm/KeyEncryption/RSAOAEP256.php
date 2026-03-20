<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Encryption\Algorithm\Key_Encryption\Util\Rsa_Crypt;
use Override;
final readonly class RSAOAEP256 extends RSA
{
    #[Override]
    public function get_encryption_mode(): int
    {
        return Rsa_Crypt::ENCRYPTION_OAEP;
    }
    #[Override]
    public function get_hash_algorithm(): string
    {
        return 'sha256';
    }
    #[Override]
    public function name(): string
    {
        return 'RSA-OAEP-256';
    }
}