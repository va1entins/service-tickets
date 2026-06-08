# Zmienna skracająca wywołanie poleceń w kontenerze PHP
PHP = docker compose exec php

.PHONY: up down restart bash logs migrate fixtures cache routes messenger

## Uruchamia kontenery w tle
up:
	docker compose up -d

## Zatrzymuje i usuwa kontenery
down:
	docker compose down

## Restartuje wszystkie kontenery
restart: down up

## Otwiera bash w kontenerze PHP
bash:
	docker compose exec php bash

## Wyświetla logi wszystkich kontenerów w trybie śledzenia
logs:
	docker compose logs -f

## Uruchamia migracje bazy danych
migrate:
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction

## Ładuje fixtures (dane testowe)
fixtures:
	$(PHP) php bin/console doctrine:fixtures:load --no-interaction

## Czyści cache aplikacji
cache:
	$(PHP) php bin/console cache:clear

## Wyświetla listę zarejestrowanych tras
routes:
	$(PHP) php bin/console debug:router

## Wyświetla zarejestrowane handlery Messengera
messenger:
	$(PHP) php bin/console debug:messenger

## Uruchamia analizę statyczną PHPStan
phpstan:
	$(PHP) vendor/bin/phpstan analyse --memory-limit=512M
