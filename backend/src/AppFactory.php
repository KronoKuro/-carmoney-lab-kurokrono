<?php

declare(strict_types=1);

namespace CarMoneyLab;

use CarMoneyLab\Domain\ApplicationValidator;
use CarMoneyLab\Domain\AssessmentService;
use CarMoneyLab\Domain\DecisionEngine;
use CarMoneyLab\Domain\LtvCalculator;
use CarMoneyLab\Domain\VehicleAge;
use CarMoneyLab\Domain\VinValidator;
use CarMoneyLab\Http\ApplicationController;
use CarMoneyLab\Repository\ApplicationRepository;
use CarMoneyLab\Support\Json;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory as SlimAppFactory;

final class AppFactory
{
    /** @param array<string,mixed>|null $rules */
    public static function create(?PDO $pdo = null, ?array $rules = null): App
    {
        $rules ??= require __DIR__ . '/../config/rules.php';
        $pdo ??= Database::connect();

        $assessment = new AssessmentService(
            new ApplicationValidator(
                $rules,
                new VinValidator($rules['vin']),
                new VehicleAge((int) date('Y')),
            ),
            new LtvCalculator(),
            new DecisionEngine($rules['ltv']),
            new VehicleAge((int) date('Y')),
        );

        $controller = new ApplicationController($assessment, new ApplicationRepository($pdo));

        $app = SlimAppFactory::create();
        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware((bool) getenv('APP_DEBUG'), true, true);

        $app->get('/health', static fn (
            ServerRequestInterface $request,
            ResponseInterface $response,
        ): ResponseInterface => Json::write($response, ['status' => 'ok', 'service' => 'carmoney-lab']));

        $app->post('/api/ltv', [$controller, 'ltv']);
        $app->post('/api/applications', [$controller, 'create']);
        $app->get('/api/applications', [$controller, 'index']);
        $app->get('/api/applications/{id}', [$controller, 'show']);

        return $app;
    }
}
