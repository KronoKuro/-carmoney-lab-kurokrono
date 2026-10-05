<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

/**
 * Проверка VIN по формальным признакам: длина, алфавит, запрещённые символы.
 * Контрольную сумму не считаем — для предварительной оценки достаточно формата.
 */
final class VinValidator
{
    /** @param array{length:int,forbidden_chars:list<string>} $config */
    public function __construct(private readonly array $config)
    {
    }

    public function isValid(string $vin): bool
    {
        $vin = strtoupper(trim($vin));

        if (strlen($vin) !== $this->config['length']) {
            return false;
        }

        if (preg_match('/^[A-Z0-9]+$/', $vin) !== 1) {
            return false;
        }

        foreach ($this->config['forbidden_chars'] as $char) {
            if (str_contains($vin, $char)) {
                return false;
            }
        }

        return true;
    }
}
