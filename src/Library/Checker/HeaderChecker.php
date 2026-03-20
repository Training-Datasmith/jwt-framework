<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

/**
 * This interface defines the contract for a header checker.
 */
interface Header_Checker
{
    /**
     * Checks if the given value matches the header parameter of the token.
     */
    public function check_header(mixed $value): void;
    /**
     * Retrieves the supported header for the token.
     */
    public function supported_header(): string;
    /**
     * Returns a boolean value indicating whether the requested resource can only be accessed with a protected header.
     */
    public function protected_header_only(): bool;
}