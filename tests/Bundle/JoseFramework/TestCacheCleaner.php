<?php

declare(strict_types=1);

namespace Jose\Tests\Bundle\JoseFramework;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class TestCacheCleaner
{
    public static function clearProjectCache(): void
    {
        $cacheDir = dirname(__DIR__, 3) . '/var/cache';
        if (! is_dir($cacheDir)) {
            return;
        }

        (new Filesystem())->remove($cacheDir);
    }
}
