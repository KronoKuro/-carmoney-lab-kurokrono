<?php

declare(strict_types=1);

namespace CarMoneyLab;

use PDO;

final class Database
{
    /**
     * Параметры подключения берём только из окружения: в репозитории
     * реквизитов нет. Значения по умолчанию — из docker-compose.yml.
     */
    public static function connect(): PDO
    {
        $dsn = getenv('DB_DSN') ?: 'mysql:host=db;port=3306;dbname=carmoney_lab;charset=utf8mb4';
        $user = getenv('DB_USER') ?: 'lab';
        $password = getenv('DB_PASSWORD') ?: 'lab';

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
