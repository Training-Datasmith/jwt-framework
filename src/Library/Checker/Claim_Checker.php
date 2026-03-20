<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

/**
 * Represents a claim checker interface.
 * Claim checkers are responsible for validating claims on a token.
 */
interface Claim_Checker
{
    /**
     * Checks if the given value matches the claim.
     */
    public function check_claim(mixed $value): void;
    /**
     * Returns the supported claim.
     */
    public function supported_claim(): string;
}