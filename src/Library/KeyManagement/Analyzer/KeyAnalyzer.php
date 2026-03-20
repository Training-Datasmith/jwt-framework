<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\JWK;
interface Key_Analyzer
{
    /**
     * This method will analyse the key and add messages to the message bag if needed.
     */
    public function analyze(JWK $jwk, Message_Bag $bag): void;
}