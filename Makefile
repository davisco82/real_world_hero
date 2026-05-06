SHELL := /bin/sh

DC := docker compose
APP := $(DC) exec app
APP_RUN := $(DC) run --rm app

.PHONY: help up down stop restart build rebuild ps logs log pull bash sh composer artisan migrate fresh seed key npm node install init setup queue-up queue-down horizon-up horizon-down

help:
	@echo "Available targets:"
	@echo "  make up            - Start containers in background"
	@echo "  make down          - Stop and remove containers"
	@echo "  make stop          - Stop containers"
	@echo "  make restart       - Restart containers"
	@echo "  make build         - Build images"
	@echo "  make rebuild       - Rebuild and start containers"
	@echo "  make ps            - Show container status"
	@echo "  make logs          - Follow logs from all services"
	@echo "  make log           - Alias to logs"
	@echo "  make bash          - Open bash shell in app container"
	@echo "  make sh            - Open sh shell in app container"
	@echo "  make composer c='...' - Run composer command"
	@echo "  make artisan c='...'  - Run artisan command"
	@echo "  make migrate       - Run migrations"
	@echo "  make fresh         - Fresh migrations with seed"
	@echo "  make seed          - Run db:seed"
	@echo "  make key           - Generate APP_KEY"
	@echo "  make npm           - Install npm deps in node container"
	@echo "  make node          - Run Vite dev server"
	@echo "  make queue-up      - Start queue worker service"
	@echo "  make queue-down    - Stop queue worker service"
	@echo "  make horizon-up    - Start Horizon service"
	@echo "  make horizon-down  - Stop Horizon service"
	@echo "  make install       - Install PHP dependencies"
	@echo "  make init          - Create fresh Laravel 12 project"
	@echo "  make setup         - First-time project setup"

up:
	$(DC) up -d

down:
	$(DC) down

stop:
	$(DC) stop

restart:
	$(DC) restart

build:
	$(DC) build

rebuild:
	$(DC) up -d --build

ps:
	$(DC) ps

logs:
	$(DC) logs -f

log: logs

pull:
	$(DC) pull

bash:
	$(APP) bash

sh:
	$(APP) sh

composer:
	$(APP_RUN) composer $(c)

artisan:
	$(APP) php artisan $(c)

migrate:
	$(APP) php artisan migrate

fresh:
	$(APP) php artisan migrate:fresh --seed

seed:
	$(APP) php artisan db:seed

key:
	$(APP) php artisan key:generate

npm:
	$(DC) run --rm node npm install

node:
	$(DC) up node

queue-up:
	$(DC) up -d queue

queue-down:
	$(DC) stop queue

horizon-up:
	$(DC) up -d horizon

horizon-down:
	$(DC) stop horizon

install:
	$(APP_RUN) composer install

init:
	rm -rf .laravel_tmp
	$(APP_RUN) composer create-project laravel/laravel .laravel_tmp "^12.0"
	rsync -a .laravel_tmp/ ./ \
		--exclude .git \
		--exclude .env \
		--exclude .env.docker.example \
		--exclude .dockerignore \
		--exclude docker \
		--exclude docker-compose.yml \
		--exclude Makefile \
		--exclude README.md \
		--exclude docs \
		--exclude requirements.md
	rm -rf .laravel_tmp

setup:
	cp -n .env.docker.example .env || true
	$(DC) up -d --build
	$(APP_RUN) composer install
	$(APP) php artisan key:generate
	$(APP) php artisan migrate
