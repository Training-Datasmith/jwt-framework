<?php

declare(strict_types=1);

namespace Jose\Tests\Bundle\JoseFramework\Functional\Encryption;

use InvalidArgumentException;
use Jose\Bundle\JoseFramework\Services\JWEDecrypterFactory as BundleJWEDecrypterFactory;
use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\JWEBuilderFactory;
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

        $algorithmManagerFactory = $container->get(AlgorithmManagerFactory::class);
        static::assertInstanceOf(AlgorithmManagerFactory::class, $algorithmManagerFactory);

        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe(
            new JWEBuilderFactory($algorithmManagerFactory)
        );

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
    public static function configuredBundleDecrypterRejectsEcdhSsTokenWhenAlgorithmsExcludeEcdhSs(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = $client->getContainer();

        $algorithmManagerFactory = $container->get(AlgorithmManagerFactory::class);
        $fixture = EcdhSsA128KwJweFixture::createEncryptedJwe(
            new JWEBuilderFactory($algorithmManagerFactory)
        );

        /** @var JWEDecrypter $decrypter */
        $decrypter = $container->get('jose.jwe_decrypter.loader2');
        static::assertFalse($decrypter->getKeyEncryptionAlgorithmManager()->has('ECDH-SS+A128KW'));

        static::assertFalse(self::attemptDecryptUsingKey(
            $decrypter,
            $fixture['jwe'],
            $fixture['receiverKey'],
            $fixture['senderKey']
        ));
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
     * @param-out null $successJwk
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
