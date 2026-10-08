<?php

declare(strict_types=1);

namespace Jose\Tests\Bundle\JoseFramework;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class KernelTestCase extends BaseKernelTestCase
{
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    protected static function bootKernel(array $options = []): KernelInterface
    {
        static::ensureKernelShutdown();
        TestCacheCleaner::clearProjectCache();

        return parent::bootKernel($options);
    }
}
