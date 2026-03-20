<?php

declare (strict_types=1);
namespace Jose\Experimental\Key_Encryption;

use Jose\Component\Encryption\Algorithm\Key_Encryption\RSA;
use Jose\Component\Encryption\Algorithm\Key_Encryption\Util\Rsa_Crypt;
use Override;
final readonly class RSAOAEP384 extends RSA
{
    #[Override]
    public function get_encryption_mode(): int
    {
        return Rsa_Crypt::ENCRYPTION_OAEP;
    }
    #[Override]
    public function get_hash_algorithm(): string
    {
        return 'sha384';
    }
    #[Override]
    public function name(): string
    {
        return 'RSA-OAEP-384';
    }
}