<?php

declare(strict_types=1);

namespace CarMoneyLab\Tests\Unit;

use CarMoneyLab\Domain\LtvCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LtvCalculatorTest extends TestCase
{
    private LtvCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new LtvCalculator();
    }

    #[DataProvider('amounts')]
    public function testCalculatesLtvInPercent(int $amount, int $marketValue, float $expected): void
    {
        // Arrange — данные из провайдера

        // Act
        $ltv = $this->calculator->calculate($amount, $marketValue);

        // Assert
        self::assertSame($expected, $ltv);
    }

    /** @return array<string,array{int,int,float}> */
    public static function amounts(): array
    {
        return [
            'половина стоимости' => [450000, 900000, 50.0],
            'треть стоимости' => [300000, 900000, 33.33],
            'больше стоимости' => [1200000, 1000000, 120.0],
        ];
    }

    public function testThrowsWhenMarketValueIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate(500000, 0);
    }

    public function testThrowsWhenAmountIsNotPositive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate(0, 900000);
    }
}
