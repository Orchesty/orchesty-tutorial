# Pipes Tutorial

## How to install
1. Clone tutorial repository `https://github.com/hanaboso/pipes-tutorial`

## How to run
- Run `make init-dev`
- Go to [http://127.0.0.10/ui](http://127.0.0.10/ui)

## How to create user
- Run `docker-compose exec backend bin/pipes u:c`

## How to enable your PHP services
1. Go to [UI Implementation](http://127.0.0.10/ui/sdk_implementations)
1. Add `php-sdk` as new Implementation

## MAC developers
1. Before `init-dev` is need to run `make .env`.
1. After that is need to edit .env file. Change `DEV_UID` and `DEV_GID` to `1001`.
1. Add alias on lo interface `sudo ifconfig lo0 alias 127.0.0.10 up`.
1. For remove run `sudo ifconfig lo0 127.0.0.10 delete`.

#### Is $(nproc) missing?
1. Run `brew install coreutils`