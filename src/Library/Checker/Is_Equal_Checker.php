<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use Override;
use function sprintf;
/**
 * This class implements a claim and header checker that checks if the value is equal to the expected value.
 * @see \Jose\Tests\Component\Checker\IsEqualCheckerTest
 */
final readonly class Is_Equal_Checker implements Claim_Checker, Header_Checker
{
    /**
     * @param string $key                 The claim or header parameter name to check.
     * @param bool   $protectedHeaderOnly [optional] Whether the header parameter MUST be protected.
     *                                    This option has no effect for claim checkers.
     */
    public function __construct(private string $key, private mixed $value, private bool $protected_header_only = true)
    {
    }
    #[Override]
    public function check_claim(mixed $value): void
    {
        if ($value !== $this->value) {
            throw new Invalid_Claim_Exception(sprintf('The "%s" claim is invalid.', $this->key), $this->key, $value);
        }
    }
    #[Override]
    public function supported_claim(): string
    {
        return $this->key;
    }
    #[Override]
    public function check_header(mixed $value): void
    {
        if ($value !== $this->value) {
            throw new Invalid_Header_Exception(sprintf('The "%s" header is invalid.', $this->key), $this->key, $value);
        }
    }
    #[Override]
    public function supported_header(): string
    {
        return $this->key;
    }
    #[Override]
    public function protected_header_only(): bool
    {
        return $this->protected_header_only;
    }
}