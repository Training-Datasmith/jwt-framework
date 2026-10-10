<?php

declare(strict_types=1);

namespace Jose\Tests\Bundle\JoseFramework\Functional\Encryption;

use InvalidArgumentException;
use Jose\Bundle\JoseFramework\Services\JWEDecrypterFactory as BundleJWEDecrypterFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Tests\Bundle\JoseFramework\WebTestCase;
use Jose\Tests\Component\Encryption\EcdhSsA128KwJweFixture;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class JweEcdhSsAllowListTest extends WebTestCase
{
    private const ALLOWED_WITHOUT_ECDH_SS = ['A128KW', 'A128GCM'];

    private const ALLOWED_WITH_ECDH_SS = ['A128KW', 'A128GCM', 'ECDH-SS+A128KW'];

    #[Test]
    public static function bundleDecrypterFactoryRejectsEcdhSsTokenWithoutAllowListedAlgorithms(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = $client->getContainer();

        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();

        $decrypterFactory = $container->get(BundleJWEDecrypterFactory::class);
        static::assertInstanceOf(BundleJWEDecrypterFactory::class, $decrypterFactory);

        $decrypter = $decrypterFactory->create(self::ALLOWED_WITHOUT_ECDH_SS);
        static::assertFalse($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));

        $successJwk = null;
        static::assertFalse(self::attemptDecryptUsingKey(
            $decrypter,
            $fixture['jwe'],
            $fixture['senderPublic'],
            $fixture['receiverKey']
        ));
        static::assertFalse(self::attemptDecryptUsingKeySet(
            $decrypter,
            $fixture['jwe'],
            $fixture['senderPublic'],
            $successJwk,
            $fixture['receiverKey']
        ));
    }

    #[Test]
    public static function bundleDecrypterFactoryAcceptsEcdhSsTokenWithAllowListedAlgorithms(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = $client->getContainer();

        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();

        $decrypterFactory = $container->get(BundleJWEDecrypterFactory::class);
        static::assertInstanceOf(BundleJWEDecrypterFactory::class, $decrypterFactory);

        $decrypter = $decrypterFactory->create(self::ALLOWED_WITH_ECDH_SS);
        static::assertTrue($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));

        static::assertTrue($decrypter->decryptUsingKey(
            $fixture['jwe'],
            $fixture['senderPublic'],
            0,
            $fixture['receiverKey']
        ));
        static::assertSame($fixture['payload'], $fixture['jwe']->getPayload());

        $keySetFixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $successJwk = null;
        static::assertTrue($decrypter->decryptUsingKeySet(
            $keySetFixture['jwe'],
            new JWKSet([$keySetFixture['senderPublic']]),
            0,
            $successJwk,
            $keySetFixture['receiverKey']
        ));
        static::assertSame($keySetFixture['payload'], $keySetFixture['jwe']->getPayload());
    }

    #[Test]
    public static function configuredBundleDecrypterRejectsEcdhSsTokenWhenAlgorithmsExcludeEcdhSs(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = $client->getContainer();

        /** @var JWEDecrypter $decrypter */
        $decrypter = $container->get('jose.jwe_decrypter.loader2');
        static::assertFalse($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));

        $keySetFixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $successJwk = null;
        static::assertFalse(self::attemptDecryptUsingKeySet(
            $decrypter,
            $keySetFixture['jwe'],
            $keySetFixture['senderPublic'],
            $successJwk,
            $keySetFixture['receiverKey']
        ));

        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        static::assertFalse(self::attemptDecryptUsingKey(
            $decrypter,
            $fixture['jwe'],
            $fixture['senderPublic'],
            $fixture['receiverKey']
        ));
    }

    #[Test]
    public static function configuredBundleDecrypterAcceptsEcdhSsTokenWhenAlgorithmsIncludeEcdhSs(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = $client->getContainer();

        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();

        /** @var JWEDecrypter $decrypter */
        $decrypter = $container->get('jose.jwe_decrypter.ecdh_ss_loader');
        static::assertTrue($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));

        static::assertTrue($decrypter->decryptUsingKey(
            $fixture['jwe'],
            $fixture['senderPublic'],
            0,
            $fixture['receiverKey']
        ));
        static::assertSame($fixture['payload'], $fixture['jwe']->getPayload());

        $keySetFixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $successJwk = null;
        static::assertTrue($decrypter->decryptUsingKeySet(
            $keySetFixture['jwe'],
            new JWKSet([$keySetFixture['senderPublic']]),
            0,
            $successJwk,
            $keySetFixture['receiverKey']
        ));
        static::assertSame($keySetFixture['payload'], $keySetFixture['jwe']->getPayload());
    }

    private static function attemptDecryptUsingKey(
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

    /**
     * @param-out null|JWK $successJwk
     */
    private static function attemptDecryptUsingKeySet(
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
