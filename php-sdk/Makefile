DC=docker-compose
DA=docker-compose exec -T php-sdk

.env:
	sed -e "s/{DEV_UID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -u); else echo '1001'; fi)/g" \
		-e "s/{DEV_GID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -g); else echo '1001'; fi)/g" \
		.env.dist > .env; \

init-dev: docker-up-force composer-install

# Docker section
docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env
	$(DC) down -v

# Composer section
composer-install:
	$(DA) composer install

composer-update:
	$(DA) composer update
	$(DA) composer update --dry-run roave/security-advisories
	$(DA) composer normalize

composer-outdated:
	$(DA) composer outdated

# App section
clear-cache:
	$(DA) rm -rf var
	$(DA) php bin/console cache:warmup --env=test

# Tests section
phpcodesniffer:
	$(DA) vendor/bin/phpcs --parallel=$$(nproc) --standard=ruleset.xml src tests

phpcodesnifferfix:
	$(DA) vendor/bin/phpcbf --parallel=$$(nproc) --standard=ruleset.xml src tests

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
	$(DA) vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p $$(nproc) --coverage-html var/coverage --cache-directory var/cache/coverage --coverage-filter src tests --exclude-group live

phpcoverage-ci:
	$(DA) ./vendor/hanaboso/php-check-utils/bin/coverage.sh -e live -c 50

fasttest: clear-cache phpcodesniffer phpstan phpunit phpintegration phpcoverage-ci

test: init-dev fasttest
