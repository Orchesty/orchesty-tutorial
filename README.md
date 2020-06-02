# Pipes Tutorial

## How to install
1. Clone tutorial repository `https://github.com/hanaboso/pipes-tutorial`

## How to run
- Run `make init-dev`
- Go to [http://127.0.0.10/ui](http://127.0.0.10/ui)

## How to create user
- Run `docker-compose exec backend bin/pipes u:c`

## MAC developers
1. Before `init-dev` is need to add  alis on lo interface `sudo ifconfig lo0 alias 127.0.0.10 up`.
2. For remove run `sudo ifconfig lo0 127.0.0.10 delete`.