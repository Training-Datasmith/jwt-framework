<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Core\JWK;
use LogicException;
use Override;
final readonly class ECDHSS extends Abstract_Ecdh
{
    #[Override]
    public function name(): string
    {
        return 'ECDH-SS';
    }
    /**
     * @param array<string, mixed> $complete_header
     * @param array<string, mixed> $additional_header_values
     */
    #[Override]
    public function get_agreement_key(int $encryption_key_length, string $algorithm, JWK $recipient_key, ?JWK $sender_key, array $complete_header = [], array &$additional_header_values = []): string
    {
        if ($sender_key === null) {
            throw new LogicException('The sender key shall be set');
        }
        $agreed_key = parent::get_agreement_key($encryption_key_length, $algorithm, $recipient_key, $sender_key, $complete_header, $additional_header_values);
        unset($additional_header_values['epk']);
        return $agreed_key;
    }
}