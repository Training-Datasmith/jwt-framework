<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function in_array;
use function is_array;
use function is_string;
use Override;
/**
 * Represents a class that checks the audience claim and header in a JWT token.
 */
final readonly class Audience_Checker implements Claim_Checker, Header_Checker
{
    private const CLAIM_NAME = 'aud';
    public function __construct(private string $audience, private bool $protected_header = false)
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
        if (is_string($value) && $value !== $this->audience) {
            throw new $class('Bad audience.', self::CLAIM_NAME, $value);
        }
        if (is_array($value) && !in_array($this->audience, $value, true)) {
            throw new $class('Bad audience.', self::CLAIM_NAME, $value);
        }
        if (!is_array($value) && !is_string($value)) {
            throw new $class('Bad audience.', self::CLAIM_NAME, $value);
        }
    }
}