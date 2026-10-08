<?php

declare(strict_types=1);

namespace Jose\Tests\Bundle\JoseFramework\Functional;

use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Tests\Bundle\JoseFramework\KernelTestCase;
use PHPUnit\Framework\Attributes\Test;
use function in_array;
use function sprintf;

/**
 * @internal
 */
final class ExperimentalAlgorithmsRegistrationTest extends KernelTestCase
{
    #[Test]
    public function experimentalAlgorithmsAreRegisteredInTheContainer(): void
    {
        self::bootKernel();
        $factory = static::getContainer()->get(AlgorithmManagerFactory::class);
        static::assertInstanceOf(AlgorithmManagerFactory::class, $factory);

        $aliases = ['HS1', 'A128CTR', 'A128CCM-16-128'];
        if (in_array('chacha20-poly1305', openssl_get_cipher_methods(), true)) {
            $aliases[] = 'chacha20-poly1305';
        }

        $manager = $factory->create($aliases);
        foreach ($aliases as $alias) {
            static::assertTrue($manager->has($alias), sprintf('Algorithm "%s" is not registered.', $alias));
        }
    }
}
