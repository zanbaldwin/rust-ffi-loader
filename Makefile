SHELL := bash
.SHELLFLAGS := -eu -o pipefail -c
.ONESHELL:
.DELETE_ON_ERROR:
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules
ifeq ($(origin .RECIPEPREFIX), undefined)
  $(error This Make does not support .RECIPEPREFIX; Please use GNU Make 4.0 or later)
endif
# The editor config for IDEs automatically converts tabs (default Make config) to spaces. Use a printable character instead of whitespace.
.RECIPEPREFIX = >
THIS_MAKEFILE_PATH:=$(word $(words $(MAKEFILE_LIST)),$(MAKEFILE_LIST))
THIS_DIR:=$(shell cd $(dir $(THIS_MAKEFILE_PATH));pwd)
THIS_MAKEFILE:=$(notdir $(THIS_MAKEFILE_PATH))

usage:
> @grep -E '(^[a-zA-Z_-]+:\s*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.?## "}; {printf "\033[32m %-30s\033[0m%s\n", $$1, $$2}' | sed -e 's/\[32m ## /[33m/'
.PHONY: usage
.SILENT: usage

vars:
> @$(foreach V,$(sort $(.VARIABLES)), $(if $(filter-out environment% default automatic, $(origin $V)),$(warning $V = $(value $V))))
.PHONY: vars
.SILENT: vars

require-root:
> [ "$$(id -u)" == "0" ] || { echo "This command must be run as root. Please retry with sudo."; exit 1; }
.PHONY: require-root
.SILENT: require-root

require-docker:
> command -v "docker" >/dev/null 2>&1 || { echo >&2 "Docker client required for command not found (PATH: \"$${PATH}\")."; exit 1; }
> docker info >/dev/null 2>&1 || { echo >&2 "Docker daemon unavailable. Perhaps retry as root/sudo?"; exit; }
> command -v "docker-compose" >/dev/null 2>&1 || { echo >&2 "Docker Compose required for command not found (PATH: \"$${PATH}\")."; exit 1; }
.PHONY: require-docker
.SILENT: require-docker

## Useful Shortcuts

# Do not use this recipe as a dependency of other recipes because it will get
# run every time, not just when the file doesn't exist.
install: ## (Re-)install all Composer dependencies
install:
> XDEBUG_MODE=off composer install --working-dir "$(THIS_DIR)"
.PHONY: install
.SILENT: install

clear: ## Clear all caches
clear: clear-stan clear-symfony
.PHONY: clear
.SILENT: clear

##
## Test

$(THIS_DIR)/vendor/autoload.php:
> XDEBUG_MODE=off composer install --working-dir "$(THIS_DIR)"
$(THIS_DIR)/vendor/bin/php-cs-fixer:
> XDEBUG_MODE=off composer install --working-dir "$(THIS_DIR)"
$(THIS_DIR)/vendor/bin/phpstan:
> XDEBUG_MODE=off composer install --working-dir "$(THIS_DIR)"

fix: ## Run code style fixing via PHP-CS-Fixer.
fix:
> XDEBUG_MODE=off PHP_CS_FIXER_IGNORE_ENV=1 "$(THIS_DIR)/vendor/bin/php-cs-fixer" fix --diff
.PHONY: fix
.SILENT: fix

stan: ## Run static analysis via PHPStan.
stan: $(THIS_DIR)/vendor/bin/phpstan
> XDEBUG_MODE=off "$(THIS_DIR)/vendor/bin/phpstan"
.PHONY: stan
.SILENT: stan

##
## Cache

clear-symfony: ## Clear the Symfony cache
clear-symfony: $(THIS_DIR)/vendor/autoload.php
> rm -rf "$(THIS_DIR)/var/cache"
> XDEBUG_MODE=off php "$(THIS_DIR)/vendor/bin/console" "cache:clear"
.PHONY: clear-cache
.SILENT: clear-cache

clear-stan: ## Clear the PHPStan result cache
clear-stan: $(THIS_DIR)/vendor/bin/phpstan
> (cd "$(THIS_DIR)"; XDEBUG_MODE=off php "$(THIS_DIR)/vendor/bin/phpstan" "clear-result-cache")
.PHONY: clear-stan
.SILENT: clear-stan
