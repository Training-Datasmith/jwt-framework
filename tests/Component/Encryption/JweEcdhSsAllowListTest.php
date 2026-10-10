<?php

declare(strict_types=1);

namespace Jose\Tests\Component\Encryption;

use InvalidArgumentException;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\JWEDecrypter;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class JweEcdhSsAllowListTest extends EncryptionTestCase
{
    private const ECDH_SS_ALGORITHMS = [
        'ECDH-SS',
        'ECDH-SS+A128KW',
        'ECDH-SS+A192KW',
        'ECDH-SS+A256KW',
    ];

    private const ALLOWED_WITHOUT_ECDH_SS = ['A128KW', 'A128GCM'];

    private const ALLOWED_WITH_ECDH_SS = ['ECDH-SS+A128KW', 'A128GCM'];

    #[Test]
    public function decryptUsingKeyRejectsEcdhSsTokenWhenAlgorithmIsNotAllowListed(): void
    {
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe($this->getJWEBuilderFactory());
        $decrypter = $this->getJWEDecrypterFactory()->create(self::ALLOWED_WITHOUT_ECDH_SS);

        $this->assertEcdhSsAlgorithmsAreExcluded($decrypter);

        static::assertFalse($this->attemptDecryptUsingKey(
            $decrypter,
            $fixture['jwe'],
            $fixture['receiverKey'],
            $fixture['senderKey']
        ));
        static::assertNull($fixture['jwe']->getPayload());
    }

    #[Test]
    public function decryptUsingKeySetRejectsEcdhSsTokenWhenAlgorithmIsNotAllowListed(): void
    {
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe($this->getJWEBuilderFactory());
        $decrypter = $this->getJWEDecrypterFactory()->create(self::ALLOWED_WITHOUT_ECDH_SS);
        $successJwk = null;

        $this->assertEcdhSsAlgorithmsAreExcluded($decrypter);

        static::assertFalse($this->attemptDecryptUsingKeySet(
            $decrypter,
            $fixture['jwe'],
            $fixture['receiverKey'],
            $successJwk,
            $fixture['senderKey']
        ));
        static::assertNull($fixture['jwe']->getPayload());
    }

    #[Test]
    public function decryptUsingKeyAcceptsEcdhSsTokenWhenAlgorithmIsAllowListed(): void
    {
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe($this->getJWEBuilderFactory());
        $decrypter = $this->getJWEDecrypterFactory()->create(self::ALLOWED_WITH_ECDH_SS);

        static::assertTrue($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));
        static::assertTrue($decrypter->decryptUsingKey(
            $fixture['jwe'],
            $fixture['receiverKey'],
            0,
            $fixture['senderKey']
        ));
        static::assertSame($fixture['payload'], $fixture['jwe']->getPayload());
    }

    private function assertEcdhSsAlgorithmsAreExcluded(JWEDecrypter $decrypter): void
    {
        foreach (self::ECDH_SS_ALGORITHMS as $algorithm) {
            static::assertFalse(
                $decrypter->getKeyEncryptionAlgorithmManager()->has($algorithm),
                sprintf('Algorithm "%s" must not be allow-listed.', $algorithm)
            );
        }
    }

    private function attemptDecryptUsingKey(
        JWEDecrypter $decrypter,
        JWE $jwe,
        JWK $receiverKey,
        JWK $senderKey
    ): bool {
        try {
            return $decrypter->decryptUsingKey($jwe, $receiverKey, 0, $senderKey);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    private function attemptDecryptUsingKeySet(
        JWEDecrypter $decrypter,
        JWE $jwe,
        JWK $receiverKey,
        ?JWK &$successJwk,
        JWK $senderKey
    ): bool {
        try {
            return $decrypter->decryptUsingKeySet(
                $jwe,
                new JWKSet([$receiverKey]),
                0,
                $successJwk,
                $senderKey
            );
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
