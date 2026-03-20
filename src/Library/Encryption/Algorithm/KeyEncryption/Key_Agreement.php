<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\Key_Encryption_Algorithm;
interface Key_Agreement extends Key_Encryption_Algorithm
{
    /**
     * Computes the agreement key.
     *
     * @param array<string, mixed> $completeHeader
     * @param array<string, mixed> $additionalHeaderValues
     */
    public function get_agreement_key(int $encryption_key_length, string $algorithm, JWK $recipient_key, ?JWK $sender_key, array $complete_header = [], array &$additional_header_values = []): string;
}