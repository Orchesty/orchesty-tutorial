# Pipes Tutorial

## How to install
1. Clone tutorial repository `https://github.com/hanaboso/pipes-tutorial`

## How to run
- Run `make init-dev`
- Go to [http://127.0.0.10/ui](http://127.0.0.10/ui)

## How to create user
- Run `docker-compose exec backend bin/pipes user:create`

## How to enable your PHP services
1. Go to [UI Services](http://127.0.0.10/ui/sdk_implementations)
1. Add `php-sdk` as new Services

## MAC developers

#### Is "nproc" missing?
1. Run `brew install coreutils`