<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

/**
 * Возраст автомобиля в полных годах.
 *
 * Считаем по году выпуска, без месяца: в заявке месяц выпуска не спрашиваем.
 */
final class VehicleAge
{
    public function __construct(private readonly int $currentYear)
    {
    }

    public function inYears(int $productionYear): int
    {
        return $this->currentYear - $productionYear;
    }
}
