<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\Key_Encryption_Algorithm;
interface Key_Agreement_With_Key_Wrapping extends Key_Encryption_Algorithm
{
    /**
     * Compute and wrap the agreement key.
     *
     * @param JWK $recipientKey The receiver's key
     * @param string $cek The CEK to wrap
     * @param int $encryption_key_length Size of the key expected for the algorithm used for data encryption
     * @param array<string, mixed> $complete_header The complete header of the JWT
     * @param array<string, mixed> $additional_header_values Set additional header values if needed
     */
    public function wrap_agreement_key(JWK $recipient_key, ?JWK $sender_key, string $cek, int $encryption_key_length, array $complete_header, array &$additional_header_values): string;
    /**
     * Unwrap and compute the agreement key.
     *
     * @param JWK $recipientKey The receiver's key
     * @param string $encrypted_cek The encrypted CEK
     * @param int $encryption_key_length Size of the key expected for the algorithm used for data encryption
     * @param array<string, mixed> $complete_header The complete header of the JWT
     *
     * @return string The decrypted CEK
     */
    public function unwrap_agreement_key(JWK $recipient_key, ?JWK $sender_key, string $encrypted_cek, int $encryption_key_length, array $complete_header): string;
}