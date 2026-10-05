<?php

declare(strict_types=1);

namespace CarMoneyLab\Repository;

use PDO;

/**
 * Хранение заявок, автомобилей и решений.
 */
final class ApplicationRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{vin:string,year:int,mileage:int,market_value:int,requested_amount:int,term_months:int} $input
     * @param array{ltv:float,decision:string,approved_limit:int} $assessment
     */
    public function save(string $applicantRef, array $input, array $assessment): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO applications (applicant_ref, requested_amount, term_months, status)
             VALUES (:applicant_ref, :amount, :term, :status)'
        );
        $statement->execute([
            ':applicant_ref' => $applicantRef,
            ':amount' => $input['requested_amount'],
            ':term' => $input['term_months'],
            ':status' => 'decided',
        ]);

        $applicationId = (int) $this->pdo->lastInsertId();

        $vehicle = $this->pdo->prepare(
            'INSERT INTO vehicles (application_id, vin, production_year, mileage_km, market_value)
             VALUES (:application_id, :vin, :year, :mileage, :market_value)'
        );
        $vehicle->execute([
            ':application_id' => $applicationId,
            ':vin' => $input['vin'],
            ':year' => $input['year'],
            ':mileage' => $input['mileage'],
            ':market_value' => $input['market_value'],
        ]);

        $decision = $this->pdo->prepare(
            'INSERT INTO decisions (application_id, ltv, decision, approved_limit)
             VALUES (:application_id, :ltv, :decision, :approved_limit)'
        );
        $decision->execute([
            ':application_id' => $applicationId,
            ':ltv' => $assessment['ltv'],
            ':decision' => $assessment['decision'],
            ':approved_limit' => $assessment['approved_limit'],
        ]);

        return $applicationId;
    }

    /** @return array<string,mixed> пустой массив, если заявки нет */
    public function find(int $id): array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.id, a.applicant_ref, a.requested_amount, a.term_months, a.status, a.created_at,
                    v.vin, v.production_year, v.mileage_km, v.market_value,
                    d.ltv, d.decision, d.approved_limit
             FROM applications a
             LEFT JOIN vehicles v ON v.application_id = a.id
             LEFT JOIN decisions d ON d.application_id = a.id
             WHERE a.id = :id'
        );
        $statement->execute([':id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? [] : $row;
    }

    /**
     * Список заявок для оператора.
     *
     * @return list<array<string,mixed>>
     */
    public function listApplications(?string $status = null, int $limit = 50): array
    {
        $sql = 'SELECT id, applicant_ref, requested_amount, term_months, status, created_at FROM applications';

        if ($status !== null && $status !== '') {
            $sql .= " WHERE status = '" . $status . "'";
        }

        $sql .= ' ORDER BY id DESC LIMIT ' . $limit;

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $vehicle = $this->pdo->prepare('SELECT vin, production_year FROM vehicles WHERE application_id = :id');
            $vehicle->execute([':id' => $row['id']]);
            $row['vehicle'] = $vehicle->fetch(PDO::FETCH_ASSOC) ?: null;

            $decision = $this->pdo->prepare('SELECT ltv, decision, approved_limit FROM decisions WHERE application_id = :id');
            $decision->execute([':id' => $row['id']]);
            $row['decision'] = $decision->fetch(PDO::FETCH_ASSOC) ?: null;

            $result[] = $row;
        }

        return $result;
    }
}
