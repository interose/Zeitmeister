LOCAL_PATH := .
PHP ?= php
COMPOSER ?= composer
SYMFONY ?= SYMFONY

SSH_PORT ?= 9122
PROJECT_NAME := Zeitmeister

.PHONY: help
help:
	@echo "Available targets:"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

.PHONY: install
install:
	$(COMPOSER) install
	$(PHP) bin/console importmap:install

.PHONY: deploy
deploy: ## Deploy the project to the remote server
	@if [ -z "$(REMOTE_USER)" ] || [ -z "$(HOST)" ] || [ -z "$(SSH_PORT)" ]; then \
  		echo "Please provide REMOTE_USER and HOST as environment variables."; \
	else \
  		echo "Deploying $(PROJECT_NAME) to $(HOST)..."; \
  		$(PHP) bin/console asset-map:compile; \
  		ssh -p $(SSH_PORT) $(REMOTE_USER)@$(HOST) "if [ -d /var/www/$(PROJECT_NAME)/public/assets ]; then rm -rf /var/www/$(PROJECT_NAME)/public/assets; fi"; \
  		rsync -e "ssh -p $(SSH_PORT) " --exclude-from "exclude-list" -avzh . $(REMOTE_USER)@$(HOST):/var/www/$(PROJECT_NAME) --delete; \
  		ssh -p $(SSH_PORT) $(REMOTE_USER)@$(HOST) "cd /var/www/$(PROJECT_NAME) && composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader"; \
  		ssh -p $(SSH_PORT) $(REMOTE_USER)@$(HOST) "rm -rf /var/www/$(PROJECT_NAME)/var/cache/prod"; \
  	fi

.PHONY: run
run: ## Start the dev server
	$(SYMFONY) server:start

.PHONY: clean
clean: ## Clean the cache and logs
	rm -rf var/cache/*
	rm -rf var/log/*
	rm -rf var/tailwind/*
