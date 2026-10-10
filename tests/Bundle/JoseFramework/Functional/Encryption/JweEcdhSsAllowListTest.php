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
    #[Test]
    public static function bundleDecrypterFactoryRejectsEcdhSsTokenWithoutAllowListedAlgorithms(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = $client->getContainer();

        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();

        $decrypterFactory = $container->get(BundleJWEDecrypterFactory::class);
        static::assertInstanceOf(BundleJWEDecrypterFactory::class, $decrypterFactory);

        $decrypter = $decrypterFactory->create(['A128KW', 'A128GCM']);
        static::assertFalse($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));

        $successJwk = null;
        static::assertFalse(self::attemptDecryptUsingKey(
            $decrypter,
            $fixture['jwe'],
            $fixture['receiverKey'],
            $fixture['senderKey']
        ));
        static::assertFalse(self::attemptDecryptUsingKeySet(
            $decrypter,
            $fixture['jwe'],
            $fixture['receiverKey'],
            $successJwk,
            $fixture['senderKey']
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

        $decrypter = $decrypterFactory->create(['ECDH-SS+A128KW', 'A128GCM']);
        static::assertTrue($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));

        static::assertTrue($decrypter->decryptUsingKey(
            $fixture['jwe'],
            $fixture['receiverKey'],
            0,
            $fixture['senderKey']
        ));
        static::assertSame($fixture['payload'], $fixture['jwe']->getPayload());

        $keySetFixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $successJwk = null;
        static::assertTrue($decrypter->decryptUsingKeySet(
            $keySetFixture['jwe'],
            new JWKSet([$keySetFixture['receiverKey']]),
            0,
            $successJwk,
            $keySetFixture['senderKey']
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
            $keySetFixture['receiverKey'],
            $successJwk,
            $keySetFixture['senderKey']
        ));

        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        static::assertFalse(self::attemptDecryptUsingKey(
            $decrypter,
            $fixture['jwe'],
            $fixture['receiverKey'],
            $fixture['senderKey']
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
            $fixture['receiverKey'],
            0,
            $fixture['senderKey']
        ));
        static::assertSame($fixture['payload'], $fixture['jwe']->getPayload());

        $keySetFixture = EcdhSsA128KwJweFixture::createEncryptedJwe();
        $successJwk = null;
        static::assertTrue($decrypter->decryptUsingKeySet(
            $keySetFixture['jwe'],
            new JWKSet([$keySetFixture['receiverKey']]),
            0,
            $successJwk,
            $keySetFixture['senderKey']
        ));
        static::assertSame($keySetFixture['payload'], $keySetFixture['jwe']->getPayload());
    }

    private static function attemptDecryptUsingKey(
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

    /**
     * @param-out null|JWK $successJwk
     */
    private static function attemptDecryptUsingKeySet(
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
