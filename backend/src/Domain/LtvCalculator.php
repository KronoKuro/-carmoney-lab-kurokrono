<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

use InvalidArgumentException;

/**
 * LTV (loan-to-value) — отношение запрошенной суммы к оценочной стоимости авто,
 * в процентах с двумя знаками.
 */
final class LtvCalculator
{
    public function calculate(int $requestedAmount, int $marketValue): float
    {
        if ($marketValue <= 0) {
            throw new InvalidArgumentException('Оценочная стоимость должна быть больше нуля');
        }

        if ($requestedAmount <= 0) {
            throw new InvalidArgumentException('Запрошенная сумма должна быть больше нуля');
        }

        return round($requestedAmount / $marketValue * 100, 2);
    }
}
