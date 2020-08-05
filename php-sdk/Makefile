DC=docker-compose
DA=docker-compose exec -T php-sdk

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -g)|g" \
		-e "s|{DOCKER_SOCKET_PATH}|$(shell test -S /var/run/docker-$${USER}.sock && echo /var/run/docker-$${USER}.sock || echo /var/run/docker.sock)|g" \
		-e "s|{PROJECT_SOURCE_PATH}|$(shell pwd)|g" \
		-e "s/{SSH_AUTH}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo '${SSH_AUTH_SOCK}' | sed 's/\//\\\//g'; else echo '\/run\/host-services\/ssh-auth.sock'; fi)/g" \
		.env.dist > .env \

init-dev: docker-up-force composer-install

# Docker section
docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env
	$(DC) down -v

# Composer section
composer-install:
	$(DA) composer install --no-suggest

composer-update:
	$(DA) composer update --no-suggest
	$(DA) composer update --dry-run roave/security-advisories
	$(DA) composer normalize

composer-outdated:
	$(DA) composer outdated

# App section
clear-cache:
	$(DA) rm -rf var/log
	$(DA) rm -rf var/coverage
	$(DA) php bin/console cache:clear --env=test
	$(DA) php bin/console cache:warmup --env=test

# Tests section
phpcodesniffer:
	$(DA) vendor/bin/phpcs --parallel=$$(nproc) --standard=ruleset.xml src tests

phpstan:
	$(DA) vendor/bin/phpstan analyse -c phpstan.neon -l 8 src tests

phpunit:
	$(DA) vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p $$(nproc) tests/Unit

phpintegration:
	$(DA) vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p $$(nproc) tests/Integration

phpcontroller:
	$(DA) vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p $$(nproc) tests/Controller

phplive:
	$(DA) vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p $$(nproc) tests/Live

phpcoverage:
	$(DA) vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p $$(nproc) --coverage-html var/coverage --whitelist /var/www/src --exclude-group live tests

phpcoverage-ci:
	$(DA) ./vendor/hanaboso/php-check-utils/bin/coverage.sh -e live

fasttest: clear-cache phpcodesniffer phpstan phpunit phpintegration phpcoverage-ci

test: init-dev fasttest