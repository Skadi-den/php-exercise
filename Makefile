-include .env.dev
export

# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@XDEBUG_MODE=debug $(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(DOCKER_COMP) exec php bash

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit $(c)


## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

hooks-install: ##@git-hooks Install or re-init git hooks
	@echo "${BLUE}Installing git hooks...${RESET}"
	@$(DOCKER_COMP) exec php vendor/bin/grumphp git:init
	#$(call run-php, ./vendor/bin/grumphp git:init)

hook-pre-commit: hook-branch-name-checker cs ##@git-hooks Run pre-commit checks

hook-commit-msg: ##@git-hooks Execute the commit linter
	$(call run-php-script, ./vendor/bin/grumphp git:commit-msg "--git-user=${GRUMPHP_GIT_USER}" "--git-email=${GRUMPHP_GIT_EMAIL}" "${GRUMPHP_COMMIT_MSG_FILE}")

hook-branch-name-checker: ##@git-hooks Check the naming of the current branch
	@echo "${BLUE}Running branch name checker ${RESET}"
	@./bin/branch-name-checker.sh

## CI ENVIRONMENT
cs-phpstan:
	@$(PHP_CONT) vendor/bin/phpstan analyse src tests

cs-deptrac:
	@$(PHP_CONT) vendor/bin/deptrac

ci-cs-fixer:
	@$(PHP_CONT) vendor/bin/php-cs-fixer fix src
	#@$(PHP_CONT) vendor/bin/php-cs-fixer fix src --dry-run
	@$(PHP_CONT) vendor/bin/php-cs-fixer fix tests
	#@$(PHP_CONT) vendor/bin/php-cs-fixer fix tests --dry-run

cs: cs-phpstan
cs: cs-deptrac
cs: ci-cs-fixer
ci: cs
ci: test
