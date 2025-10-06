.DEFAULT_GOAL := help

help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

remove-orphan-containers:
	docker compose down --remove-orphans

DOCKER_RUN_72 = docker compose run --rm -T php72 bash -lc
DOCKER_RUN_81 = docker compose run --rm -T php81 bash -lc

php72-install-dependencies: remove-orphan-containers
	$(DOCKER_RUN_72) '\
		rm -f composer.lock && \
	  	composer self-update --2 && \
	  	composer remove --dev phpunit/phpunit --no-update || true && \
	  	composer require --dev "phpunit/phpunit:^8.5" --no-update && \
		composer install --no-interaction --prefer-dist \
	'

php72-run-units: php72-install-dependencies
	$(DOCKER_RUN_72) ' \
		php -v && \
		vendor/bin/phpunit -c Test/phpunit8.xml --testsuite unit \
	'

php81-install-dependencies: remove-orphan-containers
	$(DOCKER_RUN_81) '\
		rm -f composer.lock && \
		composer remove --dev phpunit/phpunit --no-update || true && \
		composer require --dev "phpunit/phpunit:^9.6" --no-update && \
		composer install --no-interaction --prefer-dist \
	'

php81-run-units: php81-install-dependencies
	$(DOCKER_RUN_81) ' \
		php -v && \
		vendor/bin/phpunit -c Test/phpunit9.xml --testsuite unit \
	'


