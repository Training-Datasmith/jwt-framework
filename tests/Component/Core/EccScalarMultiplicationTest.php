<?php

declare(strict_types=1);

namespace Jose\Tests\Component\Core;

use Brick\Math\BigInteger;
use Jose\Component\Core\Util\Ecc\Curve;
use Jose\Component\Core\Util\Ecc\NistCurve;
use Jose\Component\Core\Util\Ecc\Point;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EccScalarMultiplicationTest extends TestCase
{
    #[Test]
    public function scalarMultiplicationYieldsPublishedP256GeneratorDouble(): void
    {
        $curve = NistCurve::curve256();
        $generator = $curve->getGenerator();
        $twoG = $curve->mul($generator, BigInteger::of(2));

        $expectedX = BigInteger::fromBase('7cf27b188d034f7e8a52380304b51ac3c08969e277f21b35a60b48fc47669978', 16);
        $expectedY = BigInteger::fromBase('07775510db8ed040293d9ac69f7430dbba7dade63ce982299e04b79d227873d1', 16);

        static::assertTrue($twoG->getX()->isEqualTo($expectedX));
        static::assertTrue($twoG->getY()->isEqualTo($expectedY));
        static::assertFalse($twoG->isInfinity());
    }

    #[Test]
    public function scalarMultiplicationMatchesDoubleAndAddOnP256(): void
    {
        $curve = NistCurve::curve256();
        $generator = $curve->getGenerator();
        $scalar = BigInteger::of(29);

        $expected = $this->multiplyByDoubleAndAdd($curve, $generator, $scalar);
        $actual = $curve->mul($generator, $scalar);

        static::assertPointsAreEqual($expected, $actual);
    }

    private function multiplyByDoubleAndAdd(Curve $curve, Point $point, BigInteger $scalar): Point
    {
        $result = Point::infinity();
        $addend = $point;
        $k = str_pad($scalar->toBase(2), $curve->getSize(), '0', STR_PAD_LEFT);

        for ($i = strlen($k) - 1; $i >= 0; --$i) {
            if ($k[$i] === '1') {
                $result = $curve->add($result, $addend);
            }
            $addend = $curve->getDouble($addend);
        }

        return $result;
    }

    private static function assertPointsAreEqual(Point $expected, Point $actual): void
    {
        static::assertTrue($expected->getX()->isEqualTo($actual->getX()));
        static::assertTrue($expected->getY()->isEqualTo($actual->getY()));
        static::assertSame($expected->isInfinity(), $actual->isInfinity());
    }
}
