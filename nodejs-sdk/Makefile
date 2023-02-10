DCS=docker-compose exec -T sdk
IMAGE=EDIT_ME:$(TAG)

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
		.env.dist > .env; \

# Build
build: .env
	docker build -f Dockerfile -t $(IMAGE) --pull .
	docker push $(IMAGE)

docker-compose.ci.yml:
	# Comment out any port forwarding
	sed -r 's/^(\s+ports:)$$/#\1/g; s/^(\s+- \$$\{DEV_IP\}.*)$$/#\1/g' docker-compose.yaml > docker-compose.ci.yml

docker-up-force: .env .lo0-up
	docker-compose pull --ignore-pull-failures
	docker-compose up -d --force-recreate --remove-orphans --build

docker-down-clean: .env .lo0-down
	docker-compose down -v

start:
	$(DCS) pnpm run start

install:
	$(DCS) pnpm install

update:
	$(DCS) pnpm update

lint:
	$(DCS) pnpm run lint

unit:
	$(DCS) pnpm run test

localtest:
	pnpm run lint
	pnpm run test

fasttest: lint unit

test: docker-up-force install fasttest docker-down-clean
