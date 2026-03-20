<?php

declare (strict_types=1);
namespace Jose\Component\Nested_Token;

use function array_key_exists;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Jwe_Builder;
use Jose\Component\Encryption\Serializer\Jwe_Serializer_Manager;
use Jose\Component\Signature\Jws_Builder;
use Jose\Component\Signature\Serializer\Jws_Serializer_Manager;
class Nested_Token_Builder
{
    public function __construct(private readonly Jwe_Builder $jwe_builder, private readonly Jwe_Serializer_Manager $jwe_serializer_manager, private readonly Jws_Builder $jws_builder, private readonly Jws_Serializer_Manager $jws_serializer_manager)
    {
    }
    /**
     * Creates a nested token.
     *
     * @param array{array{key: JWK, protected_header?: array<string, mixed>, header?: array<string, mixed>}} $signatures
     * @param array{alg?: string, string?: mixed} $jweSharedProtectedHeader
     * @param array{alg?: string, string?: mixed} $jweSharedHeader
     * @param array{array{key: JWK, header?: array<string, mixed>}} $recipients
     */
    public function create(string $payload, array $signatures, string $jws_serialization_mode, array $jwe_shared_protected_header, array $jwe_shared_header, array $recipients, string $jwe_serialization_mode, ?string $aad = null): string
    {
        $jws = $this->jws_builder->create()->with_payload($payload);
        foreach ($signatures as $signature) {
            $signature['protected_header'] = array_key_exists('protected_header', $signature) ? $signature['protected_header'] : [];
            $signature['header'] = array_key_exists('header', $signature) ? $signature['header'] : [];
            $jws = $jws->add_signature($signature['key'], $signature['protected_header'], $signature['header']);
        }
        $jws = $jws->build();
        $token = $this->jws_serializer_manager->serialize($jws_serialization_mode, $jws);
        $jwe_shared_protected_header['cty'] = 'JWT';
        $jwe = $this->jwe_builder->create()->with_payload($token)->with_shared_protected_header($jwe_shared_protected_header)->with_shared_header($jwe_shared_header)->with_aad($aad);
        foreach ($recipients as $recipient) {
            $recipient['header'] = array_key_exists('header', $recipient) ? $recipient['header'] : [];
            $jwe = $jwe->add_recipient($recipient['key'], $recipient['header']);
        }
        $jwe = $jwe->build();
        return $this->jwe_serializer_manager->serialize($jwe_serialization_mode, $jwe);
    }
}