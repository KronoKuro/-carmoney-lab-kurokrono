<?php

declare(strict_types=1);

/**
 * Справочник бизнес-правил предварительной оценки.
 *
 * Все числа здесь — учебные и синтетические. Правила вынесены в конфиг,
 * чтобы их можно было менять без правки кода: код читает эти значения,
 * а не хранит их у себя.
 */

return [
    'vin' => [
        'length' => 17,
        // В настоящем VIN не встречаются I, O и Q — их путают с 1 и 0.
        'forbidden_chars' => ['I', 'O', 'Q'],
    ],

    'vehicle' => [
        'min_year' => 1990,
        'max_age_years' => 20,
        'max_mileage_km' => 500000,
    ],

    'amount' => [
        'min' => 50000,
        'max' => 2000000,
    ],

    'term' => [
        'min_months' => 3,
        'max_months' => 48,
    ],

    /**
     * Пороги решения по LTV (loan-to-value, проценты).
     *
     *   LTV <= approve_max        -> approve
     *   approve_max < LTV <= review_max -> review
     *   LTV > review_max          -> reject
     */
    'ltv' => [
        'approve_max' => 60.0,
        'review_max' => 85.0,
    ],

    /**
     * Максимальный LTV по возрасту автомобиля (проценты).
     * Справочник заполнен, но лимит суммы по нему пока не считается —
     * это задача LOAN-12.
     */
    'ltv_by_age' => [
        ['max_age' => 3, 'max_ltv' => 70.0],
        ['max_age' => 7, 'max_ltv' => 60.0],
        ['max_age' => 12, 'max_ltv' => 50.0],
        ['max_age' => 20, 'max_ltv' => 40.0],
    ],
];
