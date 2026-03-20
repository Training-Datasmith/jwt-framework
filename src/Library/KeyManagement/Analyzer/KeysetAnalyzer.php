<?php

declare (strict_types=1);
namespace Jose\Component\Key_Management\Analyzer;

use Jose\Component\Core\Jwk_Set;
interface Keyset_Analyzer
{
    /**
     * This method will analyse the key set and add messages to the message bag if needed.
     */
    public function analyze(Jwk_Set $jwk_set, Message_Bag $bag): void;
}