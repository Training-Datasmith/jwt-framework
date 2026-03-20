<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use function is_float;
use function is_int;
use Override;
use Psr\Clock\Clock_Interface;
/**
 * This class is a claim checker. When the "iat" is present, it will compare the value with the current timestamp.
 */
final readonly class Issued_At_Checker implements Claim_Checker, Header_Checker
{
    private const NAME = 'iat';
    public function __construct(private Clock_Interface $clock, private int $allowed_time_drift = 0, private bool $protected_header_only = false)
    {
    }
    #[Override]
    public function check_claim(mixed $value): void
    {
        if (!is_float($value) && !is_int($value)) {
            throw new Invalid_Claim_Exception('"iat" must be an integer.', self::NAME, $value);
        }
        $now = $this->clock->now()->get_timestamp();
        if ($now < $value - $this->allowed_time_drift) {
            throw new Invalid_Claim_Exception('The JWT is issued in the future.', self::NAME, $value);
        }
    }
    #[Override]
    public function supported_claim(): string
    {
        return self::NAME;
    }
    #[Override]
    public function check_header(mixed $value): void
    {
        if (!is_float($value) && !is_int($value)) {
            throw new Invalid_Header_Exception('The header "iat" must be an integer.', self::NAME, $value);
        }
        $now = $this->clock->now()->get_timestamp();
        if ($now < $value - $this->allowed_time_drift) {
            throw new Invalid_Header_Exception('The JWT is issued in the future.', self::NAME, $value);
        }
    }
    #[Override]
    public function supported_header(): string
    {
        return self::NAME;
    }
    #[Override]
    public function protected_header_only(): bool
    {
        return $this->protected_header_only;
    }
}