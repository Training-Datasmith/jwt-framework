<?php

declare(strict_types=1);

namespace Jose\Tests\Component\Encryption;

use Jose\Component\Core\JWK;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Serializer\CompactSerializer;

/**
 * Fixed P-256 receiver and sender keys and a single ECDH-SS+A128KW / A128GCM compact JWE.
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

    private const COMPACT_TOKEN = 'eyJhbGciOiJFQ0RILVNTK0ExMjhLVyIsImVuYyI6IkExMjhHQ00iLCJlcGsiOnsia3R5IjoiRUMiLCJjcnYiOiJQLTI1NiIsIngiOiI2MUxvRGNpVl91OTZFN2kwMjZfV1J0WUlGQkthZVJXS3JNMWZ5a2JEZzBBIiwieSI6IkgtYm90T2hITmRnX0VNbnhuaTVYV1M5NnQzLVdjVC1iQmdrYVh5NGlra0kifX0.Nrs5vR3B7mMx2qSpuYWXn0cfgDcOtYoO.mk4qLF0HaITChilW.Xlj5eRBVdTf0216al9D-8nicDX_o7OOAhxdREv3T.yGzZI0eL1yY4FeA5P6i0Mw';

    /**
     * @return array{jwe: JWE, receiverKey: JWK, senderKey: JWK, payload: string}
     */
    public static function createEncryptedJwe(): array
    {
        $jwe = (new CompactSerializer())->unserialize(self::COMPACT_TOKEN);

        return [
            'jwe' => $jwe,
            'receiverKey' => new JWK(self::RECEIVER_KEY_PARAMETERS),
            'senderKey' => new JWK(self::SENDER_KEY_PARAMETERS),
            'payload' => self::PAYLOAD,
        ];
    }
}
