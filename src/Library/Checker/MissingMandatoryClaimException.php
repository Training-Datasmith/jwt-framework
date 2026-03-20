<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use Exception;
/**
 * This exception is thrown by claim checkers when a mandatory claim is missing.
 */
class Missing_Mandatory_Claim_Exception extends Exception implements Claim_Exception_Interface
{
    /**
     * @param string[] $claims
     */
    public function __construct(string $message, private readonly array $claims)
    {
        parent::__construct($message);
    }
    /**
     * @return string[]
     */
    public function get_claims(): array
    {
        return $this->claims;
    }
}