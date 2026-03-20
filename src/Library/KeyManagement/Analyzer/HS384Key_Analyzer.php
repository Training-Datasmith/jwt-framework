<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Override;
final readonly class Hs384key_Analyzer extends Hs_Key_Analyzer
{
    #[Override]
    protected function get_algorithm_name(): string
    {
        return 'HS384';
    }
    #[Override]
    protected function get_minimum_key_size(): int
    {
        return 384;
    }
}