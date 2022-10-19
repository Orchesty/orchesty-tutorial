DC=docker-compose
DR=docker-compose exec -T rabbitmq
DB=docker-compose exec -T backend
PHP_SDK=docker-compose exec -T php-sdk
NODE_SDK=docker-compose exec -T nodejs-sdk

ALIAS?=alias
Darwin:
	sudo ifconfig lo0 $(ALIAS) $(shell awk '$$1 ~ /^DEV_IP/' .env.dist | sed -e "s/^DEV_IP=//")
Linux:
	@echo 'skipping ...'
.lo0-up:
	-@make `uname`
.lo0-down:
	-@make `uname` ALIAS='-alias'
.env:
	sed -e "s/{DEV_UID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -u); else echo '1001'; fi)/g" \
		-e "s/{DEV_GID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -g); else echo '1001'; fi)/g" \
		-e "s/{SSH_AUTH}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo '${SSH_AUTH_SOCK}' | sed 's/\//\\\//g'; else echo '\/run\/host-services\/ssh-auth.sock'; fi)/g" \
		-e "s|{DOCKER_SOCKET_PATH}|$(shell test -S /var/run/docker-$${USER}.sock && echo /var/run/docker-$${USER}.sock || echo /var/run/docker.sock)|g" \
		-e "s|{PROJECT_SOURCE_PATH}|$(shell pwd)|g" \
		.env.dist > .env; \

init-dev: docker-up-force composer-install clear-cache

# Docker section
docker-up-force: .env .lo0-up
	$(DC) pull --ignore-pull-failures
	$(DC) up -d --force-recreate --remove-orphans
	$(DC) run --rm wait-for-it rabbitmq:15672 -t 600
	$(DR) rabbitmq-plugins enable rabbitmq_consistent_hash_exchange
	$(DB) bin/console doctrine:mongodb:schema:update --dm default
	$(DB) bin/console doctrine:mongodb:schema:update --dm metrics
	$(DB) bin/console mongodb:index:update
	$(DB) bin/console service:install nodejs-sdk nodejs-sdk:8080
	$(DB) bin/console service:install php-sdk php-sdk:80
	$(DB) bin/console topology:install -c -u --force nodejs-sdk:8080

docker-down-clean: .env .lo0-down
	$(DC) down -v

docker-stop: .env .lo0-down
	$(DC) down

# Composer section
composer-install:
	$(PHP_SDK) composer install

composer-update:
	$(PHP_SDK) composer update

# Yarn section
pnpm-install:
	$(NODE_SDK) pnpm install

# App section
clear-cache:
	$(DB) bin/console doctrine:mongodb:schema:update --dm default
	$(PHP_SDK) rm -rf var/log
	$(PHP_SDK) php bin/console cache:clear --env=dev
	$(PHP_SDK) php bin/console cache:warmup --env=dev

