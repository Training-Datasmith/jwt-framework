<?php

declare (strict_types=1);
namespace Jose\Experimental\Signature;

use Jose\Component\Signature\Algorithm\ECDSA;
use Override;
final readonly class ES256K extends ECDSA
{
    #[Override]
    public function name(): string
    {
        return 'ES256K';
    }
    #[Override]
    protected function get_hash_algorithm(): string
    {
        return 'sha256';
    }
    #[Override]
    protected function get_signature_part_length(): int
    {
        return 64;
    }
}