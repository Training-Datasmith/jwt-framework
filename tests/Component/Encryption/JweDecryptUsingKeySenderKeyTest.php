<?php

declare(strict_types=1);

namespace Jose\Tests\Component\Encryption;

use Jose\Component\Core\Util\ECKey;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class JweDecryptUsingKeySenderKeyTest extends EncryptionTestCase
{
    #[Test]
    public function decryptUsingKeyForwardsSenderKeyForEcdhSsA128Kw(): void
    {
        $receiverKey = ECKey::createECKey('P-256');
        $senderKey = ECKey::createECKey('P-256');
        $payload = 'ECDH-SS sender key must be forwarded by decryptUsingKey';

        $jweBuilder = $this->getJWEBuilderFactory()
            ->create(['ECDH-SS+A128KW', 'A128GCM']);
        $jweDecrypter = $this->getJWEDecrypterFactory()
            ->create(['ECDH-SS+A128KW', 'A128GCM']);

        $jwe = $jweBuilder
            ->create()
            ->withPayload($payload)
            ->withSharedProtectedHeader([
                'alg' => 'ECDH-SS+A128KW',
                'enc' => 'A128GCM',
            ])
            ->addRecipient($receiverKey->toPublic())
            ->withSenderKey($senderKey)
            ->build();

        static::assertTrue($jweDecrypter->decryptUsingKey($jwe, $senderKey->toPublic(), 0, $receiverKey));
        static::assertSame($payload, $jwe->getPayload());
    }
}
