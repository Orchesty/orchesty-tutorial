# Pipes Tutorial

## How to install
1. Clone tutorial repository `https://github.com/hanaboso/pipes-tutorial`

## How to run
- Run `make init-dev`
- Go to [http://127.0.0.10](http://127.0.0.10)

## How to create user
- Run `docker-compose exec backend bin/pipes user:create`

## How to enable your PHP services
1. Go to [UI Services](http://127.0.0.10/services)
2. Add `php-sdk` as new Services where:
    1. URL: `php-sdk:80`
    1. Name: `php-sdk`

## How to enable your Node.JS services
1. Go to [UI Services](http://127.0.0.10/services)
2. Add `node-sdk` as new Services
    1. URL: `node-sdk:8080`
    2. Name: `node-sdk`

## MacOs developers

#### Is "nproc" missing?
1. Run `brew install coreutils`
