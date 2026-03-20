<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use Exception;
/**
 * This exception is thrown by claim checkers when a claim check failed.
 */
class Invalid_Claim_Exception extends Exception implements Claim_Exception_Interface
{
    public function __construct(string $message, private readonly string $claim, private readonly mixed $value)
    {
        parent::__construct($message);
    }
    /**
     * Returns the claim that caused the exception.
     */
    public function get_claim(): string
    {
        return $this->claim;
    }
    /**
     * Returns the claim value that caused the exception.
     */
    public function get_value(): mixed
    {
        return $this->value;
    }
}