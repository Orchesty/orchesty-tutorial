DC=docker-compose
PHP_SDK=docker-compose exec -T php-sdk

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
	$(PHP_SDK) composer install --no-suggest