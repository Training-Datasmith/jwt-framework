<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function in_array;
use function is_string;
use Override;
/**
 * AlgorithmChecker class.
 *
 * This class implements the HeaderChecker interface and is responsible for checking the "alg" header in a token.
 */
final readonly class Algorithm_Checker implements Header_Checker
{
    private const HEADER_NAME = 'alg';
    /**
     * @param string[] $supportedAlgorithms
     */
    public function __construct(private array $supported_algorithms, private bool $protected_header = false)
    {
    }
    #[Override]
    public function check_header(mixed $value): void
    {
        if (!is_string($value)) {
            throw new Invalid_Header_Exception('"alg" must be a string.', self::HEADER_NAME, $value);
        }
        if (!in_array($value, $this->supported_algorithms, true)) {
            throw new Invalid_Header_Exception('Unsupported algorithm.', self::HEADER_NAME, $value);
        }
    }
    #[Override]
    public function supported_header(): string
    {
        return self::HEADER_NAME;
    }
    #[Override]
    public function protected_header_only(): bool
    {
        return $this->protected_header;
    }
}