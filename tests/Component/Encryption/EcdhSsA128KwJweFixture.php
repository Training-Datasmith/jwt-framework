<?php

declare(strict_types=1);

namespace Jose\Tests\Component\Encryption;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\ECKey;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\JWEBuilderFactory;

/**
 * @internal
 */
final class EcdhSsA128KwJweFixture
{
    /**
     * @return array{jwe: JWE, receiverKey: JWK, senderKey: JWK, payload: string}
     */
    public static function createEncryptedJwe(JWEBuilderFactory $builderFactory): array
    {
        $receiverKey = ECKey::createECKey('P-256');
        $senderKey = ECKey::createECKey('P-256');
        $payload = 'ECDH-SS allow-list enforcement';

        $jweBuilder = $builderFactory->create(['ECDH-SS+A128KW', 'A128GCM']);
        $jwe = $jweBuilder
            ->create()
            ->withPayload($payload)
            ->withSharedProtectedHeader([
                'alg' => 'ECDH-SS+A128KW',
                'enc' => 'A128GCM',
                'epk' => $senderKey->toPublic()->all(),
            ])
            ->addRecipient($receiverKey->toPublic())
            ->withSenderKey($senderKey)
            ->build();

        return [
            'jwe' => $jwe,
            'receiverKey' => $receiverKey,
            'senderKey' => $senderKey,
            'payload' => $payload,
        ];
    }
}
