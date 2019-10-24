# Manual
# make - runs wordpress
# make


WPCLI_CONTINER_NAME := wp-cli
WPAPP_CONTINER_NAME := wordpress

# ----------------------------------------------------------------

all: up wp-install wp-plugins

# ----------------------------------------------------------------
wp-install:
	docker-compose run  \
			--rm "$(WPCLI_CONTINER_NAME)" wp core install \
			--allow-root \
			--url=127.0.0.1:8080 \
			--title=development \
			--admin_user=root \
			--admin_password=root \
			--admin_email=root@root.com

## Install debug bar and developer
wp-plugins:
	docker-compose run  \
		--rm "$(WPCLI_CONTINER_NAME)" wp plugin install \
		--activate --allow-root --force \
		https://downloads.wordpress.org/plugin/debug-bar.1.0.zip \
		https://downloads.wordpress.org/plugin/developer.1.2.6.zip

# ----------------------------------------------------------------

## Start all containers (in background) for development
up:
	docker-compose  up --no-recreate -d
	@sleep 10

## Destroy Setup
down:
	docker-compose  down -v

# ----------------------------------------------------------------

# Install packages required by NPM
npm:
	npm install

# Runs NPM Gulp builder
# builds css/js and generate hashes files.
gulp: npm
	npm run gulp
