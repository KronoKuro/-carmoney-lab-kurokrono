SHELL := /bin/bash
COMPOSE := docker compose

# Тесты и линтер работают двумя способами: локальным PHP, если он есть,
# иначе внутри контейнера backend. Зависимости при необходимости ставятся сами.
LOCAL_PHPUNIT := $(shell test -x vendor/bin/phpunit && command -v php >/dev/null 2>&1 && echo yes)
LOCAL_PHP := $(shell command -v php >/dev/null 2>&1 && command -v composer >/dev/null 2>&1 && echo yes)

.DEFAULT_GOAL := help

.PHONY: help up down logs test lint install seed ps

help: ## Показать список команд
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

up: ## Поднять сервис и базу (http://localhost:8080)
	$(COMPOSE) up -d --build
	@echo "Сервис: http://localhost:$${APP_PORT:-8080}/  ·  health: http://localhost:$${APP_PORT:-8080}/health"

down: ## Остановить сервис (данные в томе остаются)
	$(COMPOSE) down

ps: ## Показать состояние контейнеров
	$(COMPOSE) ps

logs: ## Логи сервиса
	$(COMPOSE) logs -f backend

install: ## Установить PHP-зависимости локально (нужен composer)
	composer install

test: ## Прогнать тесты PHPUnit
ifeq ($(LOCAL_PHPUNIT),yes)
	vendor/bin/phpunit --colors=always
else ifeq ($(LOCAL_PHP),yes)
	@echo "==> зависимостей нет, ставлю их локально"
	composer install --no-interaction --no-progress
	vendor/bin/phpunit --colors=always
else
	$(COMPOSE) run --rm --no-deps backend vendor/bin/phpunit --colors=always
endif

lint: ## Проверить синтаксис PHP во всех исходниках и тестах
ifeq ($(LOCAL_PHP),yes)
	@find backend tests -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null && echo "php -l: ошибок нет"
else
	$(COMPOSE) run --rm --no-deps backend bash -lc "find backend tests -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null && echo 'php -l: ошибок нет'"
endif

seed: ## Перезалить учебные данные в уже поднятую базу
	$(COMPOSE) exec -T db mysql -ulab -plab carmoney_lab < db/seed.sql
