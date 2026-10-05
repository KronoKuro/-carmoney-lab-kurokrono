<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

/**
 * Валидация входящей заявки по справочнику правил.
 * Возвращает нормализованные значения или бросает ValidationException.
 */
final class ApplicationValidator
{
    public function __construct(
        private readonly array $rules,
        private readonly VinValidator $vinValidator,
        private readonly VehicleAge $vehicleAge,
    ) {
    }

    /**
     * @param array<string,mixed> $payload
     * @return array{vin:string,year:int,mileage:int,market_value:int,requested_amount:int,term_months:int}
     */
    public function validate(array $payload): array
    {
        $errors = [];

        $vin = strtoupper(trim((string) ($payload['vin'] ?? '')));
        if (!$this->vinValidator->isValid($vin)) {
            $errors['vin'] = 'VIN должен состоять из 17 символов A-Z и 0-9 без букв I, O, Q';
        }

        $year = (int) ($payload['year'] ?? 0);
        $age = $this->vehicleAge->inYears($year);
        if ($year < $this->rules['vehicle']['min_year']) {
            $errors['year'] = sprintf('Год выпуска не раньше %d', $this->rules['vehicle']['min_year']);
        } elseif ($age < 0) {
            $errors['year'] = 'Год выпуска не может быть в будущем';
        } elseif ($age > $this->rules['vehicle']['max_age_years']) {
            $errors['year'] = sprintf('Возраст авто больше %d лет', $this->rules['vehicle']['max_age_years']);
        }

        $mileage = (int) ($payload['mileage'] ?? -1);
        if ($mileage < 0 || $mileage > $this->rules['vehicle']['max_mileage_km']) {
            $errors['mileage'] = sprintf('Пробег от 0 до %d км', $this->rules['vehicle']['max_mileage_km']);
        }

        $marketValue = (int) ($payload['market_value'] ?? 0);
        if ($marketValue <= 0) {
            $errors['market_value'] = 'Оценочная стоимость должна быть больше нуля';
        }

        $amount = (int) ($payload['requested_amount'] ?? 0);
        if ($amount < $this->rules['amount']['min'] || $amount > $this->rules['amount']['max']) {
            $errors['requested_amount'] = sprintf(
                'Сумма от %d до %d рублей',
                $this->rules['amount']['min'],
                $this->rules['amount']['max'],
            );
        }

        $term = (int) ($payload['term_months'] ?? 0);
        if ($term < $this->rules['term']['min_months'] || $term > $this->rules['term']['max_months']) {
            $errors['term_months'] = sprintf(
                'Срок от %d до %d месяцев',
                $this->rules['term']['min_months'],
                $this->rules['term']['max_months'],
            );
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'vin' => $vin,
            'year' => $year,
            'mileage' => $mileage,
            'market_value' => $marketValue,
            'requested_amount' => $amount,
            'term_months' => $term,
        ];
    }
}
