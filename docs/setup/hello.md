Готов.

Учебный сервис предварительной оценки заявки на заём под ПТС (VIN, год выпуска, пробег, оценочная стоимость, сумма, срок): считает LTV и возвращает решение approve / review / reject; все данные синтетические.
Makefile: make up, make down, make ps, make logs, make install, make test (PHPUnit), make lint (php -l), make seed, make help; в docker-compose.yml — только запуск сервисов backend (PHP, порт ${APP_PORT:-8080}) и db (MySQL 8.0 с healthcheck), команд проверки в нём не нашёл.
Решение считается в backend/src/Domain/ — там DecisionEngine.php и AssessmentService.php.

«модель: training-2026-09-glm-5.3».