<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use Jose\Component\Core\JWK;
use Override;
abstract readonly class ECDHSSAESKW extends Abstract_Ecdhaeskw
{
    /**
     * @param array<string, mixed> $complete_header
     * @param array<string, mixed> $additional_header_values
     */
    #[Override]
    public function wrap_agreement_key(JWK $recipient_key, ?JWK $sender_key, string $cek, int $encryption_key_length, array $complete_header, array &$additional_header_values): string
    {
        $ecdh_ss = new ECDHSS();
        $agreement_key = $ecdh_ss->get_agreement_key($this->get_key_length(), $this->name(), $recipient_key->to_public(), $sender_key, $complete_header, $additional_header_values);
        $wrapper = $this->get_wrapper();
        return $wrapper::wrap($agreement_key, $cek);
    }
    /**
     * @param array<string, mixed> $complete_header
     */
    #[Override]
    public function unwrap_agreement_key(JWK $recipient_key, ?JWK $sender_key, string $encrypted_cek, int $encryption_key_length, array $complete_header): string
    {
        $ecdh_ss = new ECDHSS();
        $agreement_key = $ecdh_ss->get_agreement_key($this->get_key_length(), $this->name(), $recipient_key, $sender_key, $complete_header);
        $wrapper = $this->get_wrapper();
        return $wrapper::unwrap($agreement_key, $encrypted_cek);
    }
}