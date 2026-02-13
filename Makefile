.PHONY: help start stop restart test clean logs db-migrate db-rollback

help: ## Показать помощь
	@echo "Доступные команды:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

start: ## Запустить контейнеры
	docker compose up -d --build
	@echo "Приложение доступно по адресу: http://localhost:8050"

stop: ## Остановить контейнеры
	docker compose down

restart: stop start ## Перезапустить контейнеры

test: ## Запустить тесты
	@echo "Запуск тестов..."
	docker compose -f docker-compose.test.yml --env-file=.env.test up --build --abort-on-container-exit

test-quick: ## Быстрый запуск тестов (без пересборки)
	docker compose -f docker-compose.test.yml up --abort-on-container-exit

clean: ## Очистить все контейнеры и volumes
	docker compose down -v
	docker compose -f docker-compose.test.yml down -v

logs: ## Показать логи приложения
	docker compose logs -f app

logs-db: ## Показать логи базы данных
	docker compose logs -f db

db-migrate: ## Выполнить миграции
	docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

db-rollback: ## Откатить миграцию
	docker compose exec app php bin/console doctrine:migrations:migrate prev

db-create: ## Создать базу данных
	docker compose exec app php bin/console doctrine:database:create

db-drop: ## Удалить базу данных
	docker compose exec app php bin/console doctrine:database:drop --force

cache-clear: ## Очистить кэш
	docker compose exec app php bin/console cache:clear

console: ## Открыть консоль в контейнере
	docker compose exec app bash

install: ## Установить зависимости
	composer install

update: ## Обновить зависимости
	composer update

phpunit: ## Запустить PHPUnit локально
	./vendor/bin/phpunit