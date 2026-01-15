.DEFAULT_GOAL := help

help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)


DOCKER_COMPOSE = docker compose -f compose.yml
DOCKER_RUN_PHP72 = $(DOCKER_COMPOSE) run --rm -T php72 bash -lc
DOCKER_RUN_PHP83 = $(DOCKER_COMPOSE) run --rm -T php83 bash -lc

##@ Cleanup
clean:  ## Remove all containers and orphans
	docker compose down --remove-orphans

clean-deps:  ## Remove all composer dependencies
	rm -rf vendor composer.lock .php72-deps-installed.cache .php83-deps-installed.cache
	docker volume rm magento-plugin_vendor-php72 magento-plugin_vendor-php83 2>/dev/null || true

##@ PHP 7.2 Tests
php72-deps:  ## Install PHP 7.2 dependencies (cached)
	@if [ ! -f ".php72-deps-installed.cache" ]; then \
		$(DOCKER_RUN_PHP72) '\
			rm -f composer.lock && \
			composer config --no-interaction audit.block-insecure false && \
			composer remove --dev phpunit/phpunit --no-update || true && \
			composer require --dev "phpunit/phpunit:^8.5" --no-update && \
			composer install --no-interaction --prefer-dist \
		' && touch .php72-deps-installed.cache; \
	else \
		echo "✓ PHP 7.2 dependencies already installed (use 'make clean-deps' to reinstall)"; \
	fi

php72-test: php72-deps  ## Run PHP 7.2 unit tests
	$(DOCKER_RUN_PHP72) 'composer test:php72'

phpstan: php72-deps  ## Run PHP 7.2 CSFixer
	$(DOCKER_RUN_PHP72) 'composer phpstan'

php72-cs-fixer: php72-deps  ## Run PHP 7.2 CSFixer
	$(DOCKER_RUN_PHP72) 'composer cs-fix'

##@ PHP 8.3 Tests
php83-deps:  ## Install PHP 8.3 dependencies (cached)
	@if [ ! -f ".php83-deps-installed.cache" ]; then \
		$(DOCKER_RUN_PHP83) '\
			rm -f composer.lock && \
			composer remove --dev phpunit/phpunit --no-update || true && \
			composer require --dev "phpunit/phpunit:^9.6" --no-update && \
			composer install --no-interaction --prefer-dist \
		' && touch .php83-deps-installed.cache; \
	else \
		echo "✓ PHP 8.3 dependencies already installed (use 'make clean-deps' to reinstall)"; \
	fi

php83-test: php83-deps  ## Run PHP 8.3 unit tests
	$(DOCKER_RUN_PHP83) 'composer test:php83'

##@ All Tests
test-all: php72-test php83-test  ## Run tests on both PHP versions

##@ Development
up:  ## Start Docker containers
	@echo "Starting Docker containers..."
	@docker compose up -d
	@echo "✓ Containers started"

setup-php72: up php72-deps sync-vendor-php72  ## Setup complete PHP 7.2 development environment
	@echo "✓ PHP 7.2 environment ready for development"

setup-php83: up php83-deps sync-vendor-php83  ## Setup complete PHP 8.3 development environment
	@echo "✓ PHP 8.3 environment ready for development"

sync-vendor-php72:  ## Copy vendor from PHP 7.2 container to local (for debugging)
	@echo "Copying vendor from PHP 7.2 container..."
	@rm -rf ./vendor
	@docker cp magento-plugin-php72:/app/vendor ./
	@echo "✓ Vendor copied to ./vendor"

sync-vendor-php83:  ## Copy vendor from PHP 8.3 container to local (for debugging)
	@echo "Copying vendor from PHP 8.3 container..."
	@rm -rf ./vendor
	@docker cp magento-plugin-php83:/app/vendor ./
	@echo "✓ Vendor copied to ./vendor"

.PHONY: help clean clean-deps php72-deps php72-test php83-deps php83-test test-all sync-vendor-php72 sync-vendor-php83 up setup-php72 setup-php83 setup-all down
