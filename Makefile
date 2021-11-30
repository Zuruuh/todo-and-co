## ----------------------------
##
## BileMo

##
## -------
## Dev Env
##
##

DOCKER_COMPOSE  = docker-compose -f docker-compose.yaml

EXEC_PHP        = $(DOCKER_COMPOSE) exec -T php /entrypoint
EXEC_JS         = $(DOCKER_COMPOSE) exec -T node /entrypoint

SYMFONY         = $(EXEC_PHP) bin/console
PHPUNIT			= $(EXEC_PHP) bin/phpunit --coverage-html dist
COMPOSER        = $(EXEC_PHP) composer
YARN        	= $(EXEC_JS) yarn

build:
	$(DOCKER_COMPOSE) pull --parallel --quiet --ignore-pull-failures 2> /dev/null
	$(DOCKER_COMPOSE) build --pull

kill:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

install: ## Install and start the project
install: .env.local build start assets db

restart: ## STop the project and restart it using latest docker images
restart: kill install

reset: ## Stop and start a fresh install of the project
reset: kill remove install

remove:
	-rm -rf vendor node_modules "*driver" drivers var

start: ## Start the containers
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate

stop: ## Stop the containers
	$(DOCKER_COMPOSE) stop

clean: ## Stop the project and remove generated files
clean: kill
	rm -rf vendor

no-docker:
	$(eval DOCKER_COMPOSE := \#)
	$(eval EXEC_PHP := )
	$(eval EXEC_JS := )

.PHONY: build kill install reset restart start stop clean no-docker remove

##
## -----
## Utils
## 
##

db: ## Setup local database and load fake data
db: .env.local vendor
	-$(SYMFONY) doctrine:database:drop --if-exists --force
	-$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) d:m:m --no-interaction --allow-no-migration
	$(SYMFONY) d:f:l --no-interaction --purge-with-truncate

migration: ## Create a new doctrine migration
migration: vendor
	$(SYMFONY) doctrine:migrations:diff

migrate: ## Migrates db to latest saved migration
migrate: vendor
	$(SYMFONY) doctrine:migration:migrate --no-interaction

db-update-schema: ## Creates a new migrations and runs it
db-update-schema: migration migrate

db-validate-schema: ## Validate the database schema
db-validate-schema: .env.local vendor
	$(SYMFONY) doctrine:schema:validate

assets: ## Run Webpack Encore to transpile assets
assets: node_modules
	$(YARN) run dev

#> Dependencies >#

composer.lock: composer.json
	$(COMPOSER) update

vendor: composer.lock
	$(COMPOSER) install

yarn.lock: package.json
	$(YARN) upgrade

node_modules: yarn.lock
	$(YARN) install
	@touch -c node_modules

#web_drivers: drivers
# 	$(EXEC_PHP) vendor/bin/bdi browser:chromium --browser-path /usr/bin/chromium-browser ./drivers

#< Dependencies <#

.env.local: .env
	@if [ -f .env.local ]; \
	then\
		echo '\033[1;41m/!\The .env file has changed. Please check your .env.local file (this message will not be displayed again)';\
		touch .env.local;\
		exit 1;\
	else\
		echo cp .env .env.local;\
		cp .env .env.local;\
	fi

.PHONY: db migration migrate db-update-schema db-validate-schema env # web_drivers

## 
## -----
## Tests
## 
## 

test-env: start vendor 

unit: ## Run all unit tests
unit: test-env
	$(PHPUNIT) --group unit

#e2e: ## Run all End-To-End tests
#e2e: test-env web_drivers
#	$(PHPUNIT) --group e2e

test: ## Run all tests in the tests/ folder
test: test-env db unit # e2e # E2E Tests are disabled for now as panther is not working in docker containers
	$(PHPUNIT)

.PHONY: test-env unit test # e2e

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## ----------------------------

.PHONY: help