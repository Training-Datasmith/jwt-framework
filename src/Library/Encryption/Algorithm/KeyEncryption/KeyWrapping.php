<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\Key_Encryption_Algorithm;
interface Key_Wrapping extends Key_Encryption_Algorithm
{
    /**
     * Encrypt the CEK.
     *
     * @param JWK $key The key used to wrap the CEK
     * @param string $cek The CEK to encrypt
     * @param array<string, mixed> $completeHeader The complete header of the JWT
     * @param array<string, mixed> $additionalHeader The complete header of the JWT
     */
    public function wrap_key(JWK $key, string $cek, array $complete_header, array &$additional_header): string;
    /**
     * Decrypt de CEK.
     *
     * @param JWK $key The key used to wrap the CEK
     * @param string $encrypted_cek The CEK to decrypt
     * @param array<string, mixed> $completeHeader The complete header of the JWT
     */
    public function unwrap_key(JWK $key, string $encrypted_cek, array $complete_header): string;
}