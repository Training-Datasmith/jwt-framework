<?php

declare(strict_types=1);

namespace Jose\Tests\Component\Encryption;

use Jose\Component\Core\JWK;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Serializer\CompactSerializer;

/**
 * Fixed P-256 sender/receiver keys and a builder-emitted ECDH-SS+A128KW / A128GCM compact JWE (no epk).
 *
 * @internal
 */
final class EcdhSsA128KwJweFixture
{
    public const PAYLOAD = 'ECDH-SS allow-list enforcement';

    /**
     * @var array<string, string>
     */
    private const RECEIVER_KEY_PARAMETERS = [
        'kty' => 'EC',
        'crv' => 'P-256',
        'd' => 'ankm0OGHpIzD0Uc9Ly-vKWaffxlmPv8PdwsK28VfHzw',
        'x' => 'Ph5poNLJysnCN84BW515zgcUH1S74GBgTL5QwlwOxMo',
        'y' => 'bkjGDiSnQLUcbzbOZ40ltu0rtXtmU8MVMs-ZSUh-ca0',
    ];

    /**
     * @var array<string, string>
     */
    private const SENDER_KEY_PARAMETERS = [
        'kty' => 'EC',
        'crv' => 'P-256',
        'd' => 'HVF0nsJzkdMPihU_nXo-73FfCfnWdMMHCPk5ZvuVzIs',
        'x' => '61LoDciV_u96E7i026_WRtYIFBKaeRWKrM1fykbDg0A',
        'y' => 'H-botOhHNdg_EMnxni5XWS96t3-WcT-bBgkaXy4ikkI',
    ];

    private const COMPACT_TOKEN = 'eyJhbGciOiJFQ0RILVNTK0ExMjhLVyIsImVuYyI6IkExMjhHQ00ifQ.iImlCBts9V0K7Y2B3XcNDPnZbTsxZwSh.EsyFiyaPxDIjHLgQ.0Xt_1MbW7H5_ZQJ7yAtNYFPS6LS9-iVcuIGtkUAp.Sm_aIgopB9z-6VPkvsMZ2w';

    /**
     * @return array{
     *     jwe: JWE,
     *     receiverKey: JWK,
     *     senderKey: JWK,
     *     senderPublic: JWK,
     *     payload: string
     * }
     */
    public static function createEncryptedJwe(): array
    {
        $receiverKey = new JWK(self::RECEIVER_KEY_PARAMETERS);
        $senderKey = new JWK(self::SENDER_KEY_PARAMETERS);
        $jwe = (new CompactSerializer())->unserialize(self::COMPACT_TOKEN);

        return [
            'jwe' => $jwe,
            'receiverKey' => $receiverKey,
            'senderKey' => $senderKey,
            'senderPublic' => $senderKey->toPublic(),
            'payload' => self::PAYLOAD,
        ];
    }
}
