<?php

declare (strict_types=1);
namespace Jose\Component\Checker;

use Exception;
class Missing_Mandatory_Header_Parameter_Exception extends Exception
{
    /**
     * @param string[] $parameters
     */
    public function __construct(string $message, private readonly array $parameters)
    {
        parent::__construct($message);
    }
    /**
     * @return string[]
     */
    public function get_parameters(): array
    {
        return $this->parameters;
    }
}