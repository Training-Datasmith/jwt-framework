<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Override;
final readonly class ECDHES extends Abstract_Ecdh
{
    #[Override]
    public function name(): string
    {
        return 'ECDH-ES';
    }
}