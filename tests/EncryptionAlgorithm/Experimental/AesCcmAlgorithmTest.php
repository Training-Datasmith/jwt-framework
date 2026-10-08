<?php

declare(strict_types=1);

namespace Jose\Tests\EncryptionAlgorithm\Experimental;

use Jose\Experimental\ContentEncryption\A128CCM_16_128;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function strlen;

/**
 * @internal
 */
final class AesCcmAlgorithmTest extends TestCase
{
    #[Test]
    public function encryptAndDecryptWithoutAdditionalAuthenticatedData(): void
    {
        $algorithm = new A128CCM_16_128();
        $cek = random_bytes(16);
        $iv = random_bytes(13);
        $plaintext = 'AES-CCM content encryption round trip';
        $protectedHeader = '';
        $tag = null;

        $ciphertext = $algorithm->encryptContent(
            $plaintext,
            $cek,
            $iv,
            null,
            $protectedHeader,
            $tag
        );

        static::assertNotNull($tag);
        static::assertSame(16, strlen($tag));

        $decrypted = $algorithm->decryptContent(
            $ciphertext,
            $cek,
            $iv,
            null,
            $protectedHeader,
            $tag
        );

        static::assertSame($plaintext, $decrypted);
    }
}
