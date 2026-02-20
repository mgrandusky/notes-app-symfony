DC      = docker compose
DC_EXEC = docker compose exec php

.DEFAULT_GOAL := help

##@ General

.PHONY: help
help: ## Display this help message
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Docker

.PHONY: build
build: ## Build Docker images
	$(DC) build

.PHONY: up
up: ## Start all containers in the background
	$(DC) up -d

.PHONY: down
down: ## Stop and remove containers
	$(DC) down

.PHONY: restart
restart: down up ## Restart all containers

.PHONY: logs
logs: ## Tail logs from all containers
	$(DC) logs -f

##@ Application

.PHONY: composer-install
composer-install: ## Install Composer dependencies
	$(DC_EXEC) composer install

.PHONY: composer-update
composer-update: ## Update Composer dependencies
	$(DC_EXEC) composer update

.PHONY: db-init
db-init: ## Create the database schema from entity metadata (first-time Docker setup)
	$(DC_EXEC) php bin/console doctrine:schema:create --no-interaction
	$(DC_EXEC) php bin/console doctrine:migrations:sync-metadata-storage --no-interaction
	$(DC_EXEC) php bin/console doctrine:migrations:version --add --all --no-interaction

.PHONY: migrate
migrate: ## Run pending Doctrine migrations
	$(DC_EXEC) php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: cache-clear
cache-clear: ## Clear the Symfony application cache
	$(DC_EXEC) php bin/console cache:clear

.PHONY: shell
shell: ## Open an interactive shell inside the PHP container
	$(DC_EXEC) /bin/sh

##@ Setup

.PHONY: setup
setup: build up composer-install db-init ## Full first-time setup: build, start, install deps, create schema
	@echo ""
	@echo "✅  Setup complete. Open http://localhost:8080 in your browser."
