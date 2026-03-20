<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function in_array;
use function is_string;
use Override;
/**
 * This class is a header parameter and claim checker.
 *
 * When the "iss" header parameter or claim is present, it will check if the value is within the allowed ones.
 */
final readonly class Issuer_Checker implements Claim_Checker, Header_Checker
{
    private const CLAIM_NAME = 'iss';
    public function __construct(private array $issuers, private bool $protected_header = false)
    {
    }
    #[Override]
    public function check_claim(mixed $value): void
    {
        $this->check_value($value, Invalid_Claim_Exception::class);
    }
    #[Override]
    public function check_header(mixed $value): void
    {
        $this->check_value($value, Invalid_Header_Exception::class);
    }
    #[Override]
    public function supported_claim(): string
    {
        return self::CLAIM_NAME;
    }
    #[Override]
    public function supported_header(): string
    {
        return self::CLAIM_NAME;
    }
    #[Override]
    public function protected_header_only(): bool
    {
        return $this->protected_header;
    }
    private function check_value(mixed $value, string $class): void
    {
        if (!is_string($value)) {
            throw new $class('Invalid value.', self::CLAIM_NAME, $value);
        }
        if (!in_array($value, $this->issuers, true)) {
            throw new $class('Unknown issuer.', self::CLAIM_NAME, $value);
        }
    }
}