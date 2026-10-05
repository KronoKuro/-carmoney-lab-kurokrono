<?php

declare(strict_types=1);

namespace CarMoneyLab\Tests\Unit;

use CarMoneyLab\Domain\VinValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VinValidatorTest extends TestCase
{
    private VinValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new VinValidator(['length' => 17, 'forbidden_chars' => ['I', 'O', 'Q']]);
    }

    #[DataProvider('vins')]
    public function testValidatesVinFormat(string $vin, bool $expected): void
    {
        self::assertSame($expected, $this->validator->isValid($vin));
    }

    /** @return array<string,array{string,bool}> */
    public static function vins(): array
    {
        return [
            'корректный VIN' => ['XTA21099998765432', true],
            'корректный в нижнем регистре' => ['xta21099998765432', true],
            'короче 17 символов' => ['XTA210999', false],
            'длиннее 17 символов' => ['XTA210999987654321', false],
            'запрещённая буква O' => ['XTA2109999876543O', false],
            'запрещённая буква I' => ['ITA21099998765432', false],
            'спецсимвол' => ['XTA-1099998765432', false],
            'пустая строка' => ['', false],
        ];
    }
}
