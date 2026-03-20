<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\Key_Encryption_Algorithm;
interface Direct_Encryption extends Key_Encryption_Algorithm
{
    /**
     * Returns the CEK.
     *
     * @param JWK $key The key used to get the CEK
     */
    public function get_cek(JWK $key): string;
}