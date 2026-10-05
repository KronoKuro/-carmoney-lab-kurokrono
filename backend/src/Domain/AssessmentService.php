<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

/**
 * Предварительная оценка заявки: валидация -> LTV -> решение -> лимит.
 *
 * Лимит сейчас равен запрошенной сумме при approve и нулю в остальных случаях.
 * Расчёт лимита по максимальному LTV для возраста авто (справочник
 * rules.ltv_by_age) — задача LOAN-12, она ещё не сделана.
 */
final class AssessmentService
{
    public function __construct(
        private readonly ApplicationValidator $validator,
        private readonly LtvCalculator $ltvCalculator,
        private readonly DecisionEngine $decisionEngine,
        private readonly VehicleAge $vehicleAge,
    ) {
    }

    /**
     * @param array<string,mixed> $payload
     * @return array{vehicle_age:int,ltv:float,decision:string,approved_limit:int,input:array<string,mixed>}
     */
    public function assess(array $payload): array
    {
        $input = $this->validator->validate($payload);

        $ltv = $this->ltvCalculator->calculate($input['requested_amount'], $input['market_value']);
        $decision = $this->decisionEngine->decide($ltv);

        return [
            'vehicle_age' => $this->vehicleAge->inYears($input['year']),
            'ltv' => $ltv,
            'decision' => $decision,
            'approved_limit' => $decision === DecisionEngine::APPROVE ? $input['requested_amount'] : 0,
            'input' => $input,
        ];
    }
}
