<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\Util\Ecc\Curve;
use Jose\Component\Core\Util\Ecc\Nist_Curve;
use Override;
final readonly class Es384key_Analyzer extends Es_Key_Analyzer
{
    #[Override]
    protected function get_algorithm_name(): string
    {
        return 'ES384';
    }
    #[Override]
    protected function get_curve_name(): string
    {
        return 'P-384';
    }
    #[Override]
    protected function get_curve(): Curve
    {
        return Nist_Curve::curve384();
    }
    #[Override]
    protected function get_key_size(): int
    {
        return 384;
    }
}