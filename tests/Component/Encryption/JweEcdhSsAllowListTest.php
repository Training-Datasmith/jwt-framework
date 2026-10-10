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

    private const ALLOWED_WITH_ECDH_SS = ['A128KW', 'A128GCM', 'ECDH-SS+A128KW'];

    #[Test]
    public function decryptUsingKeyRejectsEcdhSsTokenWhenAlgorithmIsNotAllowListed(): void
    {
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $decrypter = $this->getJWEDecrypterFactory()->create(self::ALLOWED_WITHOUT_ECDH_SS);

        $this->assertEcdhSsAlgorithmsAreExcluded($decrypter);

        static::assertFalse($this->attemptDecryptUsingKey(
            $decrypter,
            $fixture['jwe'],
            $fixture['senderPublic'],
            $fixture['receiverKey']
        ));
    }

    #[Test]
    public function decryptUsingKeySetRejectsEcdhSsTokenWhenAlgorithmIsNotAllowListed(): void
    {
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $decrypter = $this->getJWEDecrypterFactory()->create(self::ALLOWED_WITHOUT_ECDH_SS);
        $successJwk = null;

        $this->assertEcdhSsAlgorithmsAreExcluded($decrypter);

        static::assertFalse($this->attemptDecryptUsingKeySet(
            $decrypter,
            $fixture['jwe'],
            $fixture['senderPublic'],
            $successJwk,
            $fixture['receiverKey']
        ));
    }

    #[Test]
    public function decryptUsingKeyAcceptsEcdhSsTokenWhenAlgorithmIsAllowListed(): void
    {
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $decrypter = $this->getJWEDecrypterFactory()->create(self::ALLOWED_WITH_ECDH_SS);

        static::assertTrue($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));
        static::assertTrue($decrypter->decryptUsingKey(
            $fixture['jwe'],
            $fixture['senderPublic'],
            0,
            $fixture['receiverKey']
        ));
        static::assertSame($fixture['payload'], $fixture['jwe']->getPayload());
    }

    #[Test]
    public function decryptUsingKeySetAcceptsEcdhSsTokenWhenAlgorithmIsAllowListed(): void
    {
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $decrypter = $this->getJWEDecrypterFactory()->create(self::ALLOWED_WITH_ECDH_SS);
        $successJwk = null;

        static::assertTrue($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));
        static::assertTrue($decrypter->decryptUsingKeySet(
            $fixture['jwe'],
            new JWKSet([$fixture['senderPublic']]),
            0,
            $successJwk,
            $fixture['receiverKey']
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
        JWK $senderPublic,
        JWK $receiverPrivate
    ): bool {
        try {
            return $decrypter->decryptUsingKey($jwe, $senderPublic, 0, $receiverPrivate);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    private function attemptDecryptUsingKeySet(
        JWEDecrypter $decrypter,
        JWE $jwe,
        JWK $senderPublic,
        ?JWK &$successJwk,
        JWK $receiverPrivate
    ): bool {
        try {
            return $decrypter->decryptUsingKeySet(
                $jwe,
                new JWKSet([$senderPublic]),
                0,
                $successJwk,
                $receiverPrivate
            );
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
