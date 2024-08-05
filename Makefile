# Include file with .env variables if exists
-include .env

# Define default values for variables
COMPOSE_FILE ?= docker compose.dev.yml
BASE_IMAGE_DOCKERFILE ?= .docker/dev/base/Dockerfile
IMAGE_REGISTRY ?= dev
IMAGE_TAG ?= latest



#-----------------------------------------------------------
# Common
#-----------------------------------------------------------

# Enter the bash session
%.exec:
	docker compose -f ${COMPOSE_FILE} exec $* /bin/sh

# Build the container
%.build:
	docker compose -f ${COMPOSE_FILE} build --no-cache $*

# Up the container
%.up:
	docker compose -f ${COMPOSE_FILE} up -d --no-deps $*

# Build and Up the container
%.bup:
	docker compose -f ${COMPOSE_FILE} up -d --no-deps --build $*

# Stop the container
%.stop:
	docker compose -f ${COMPOSE_FILE} stop $*

# Stop and remove containers
%.down:
	docker compose -f ${COMPOSE_FILE} down --remove-orphans $*

# Reload service
%.reload:
	docker compose -f ${COMPOSE_FILE} reload $*

# Restart container
%.restart:
	docker compose -f ${COMPOSE_FILE} restart $*

# Show container logs
%.logs:
	docker compose -f ${COMPOSE_FILE} logs $* --tail 500


#-----------------------------------------------------------
# All
#-----------------------------------------------------------

# Init variables for development environment
all.env.dev:
	cp .env.dev .env && \
	cp ./src/api/.env.dev ./src/api/.env

# Init variables for production environment
all.env.prod:
	cp .env.prod .env && \
	cp ./src/api/.env.prod ./src/api/.env

# Init variables for stage environment
all.env.stage:
	cp .env.stage .env && \
	cp ./src/api/.env.stage ./src/api/.env


#-----------------------------------------------------------
# Mysql
#-----------------------------------------------------------

# Enter the mysql container
mysql:
	docker compose -f ${COMPOSE_FILE} exec mysql /bin/bash

# Dump database into file (only for development environment) (TODO: replace file name with env variable)
mysql.dump:
	docker compose -f ${COMPOSE_FILE} exec mysql mysql -U ${DB_USERNAME} -d ${DB_DATABASE} > ./.docker/stage/mysql/dumps/dump.sql

# Import db dump
mysql.import.%:
	docker compose -f ${COMPOSE_FILE} exec -T mysql mysql -uroot -p${DB_PASSWORD} app < ./.docker/$*/mysql/dumps/init.sql


#-----------------------------------------------------------
# PhpMyAdmin
#-----------------------------------------------------------

# Enter the phpmyadmin container
phpmyadmin:
	docker compose -f ${COMPOSE_FILE} exec phpmyadmin /bin/bash

#-----------------------------------------------------------
# Frontend
#-----------------------------------------------------------

# Copy frontend source files to container
copy.frontend:
	docker cp ./src/frontend/. frontend:/var/www/html/

# Init variables for development environment
env.frontend.dev:
	cp ./src/frontend/.env.dev ./src/frontend/.env

# Init variables for production environment
env.frontend.prod:
	cp ./src/frontend/.env.prod ./src/frontend/.env

# Init variables for stage environment
env.frontend.stage:
	cp ./src/frontend/.env.stage ./src/frontend/.env

# Install yarn dependencies
yarn.install:
	docker compose -f ${COMPOSE_FILE} exec frontend yarn install

# Alias to install yarn dependencies
yi: yarn.install

# Upgrade yarn dependencies
yarn.upgrade:
	docker compose -f ${COMPOSE_FILE} exec frontend yarn upgrade

# Alias to upgrade yarn dependencies
yu: yarn.upgrade

# Show outdated yarn dependencies
yarn.outdated:
	docker compose exec -f ${COMPOSE_FILE} frontend yarn outdated

# Install yarn dependencies
yarn.build:
	docker compose -f ${COMPOSE_FILE} exec frontend yarn build

# Install yarn dependencies
yarn.start:
	docker compose -f ${COMPOSE_FILE} exec -d frontend yarn start

#-----------------------------------------------------------
# Nginx
#-----------------------------------------------------------

# Enter the nginx bash session
nginx:
	docker compose -f ${COMPOSE_FILE} exec nginx /bin/sh

# Reload the Nginx service
reload:
	docker compose -f ${COMPOSE_FILE} exec nginx nginx -s reload


#-----------------------------------------------------------
# Certbot
#-----------------------------------------------------------

#-----------------------------------------------------------
# SSL
#-----------------------------------------------------------

# Issue SSL certificates according to the environment variables
ssl.cert:
	docker compose -f ${COMPOSE_FILE} run --rm --no-deps \
		--publish 80:80 \
		certbot \
		certbot certonly \
		--domains ${LETSENCRYPT_DOMAINS} \
		--email ${LETSENCRYPT_EMAIL} \
		--agree-tos \
		--no-eff-email \
		--standalone

# Issue testing SSL certificates according to the environment variables
ssl.cert.test:
	docker compose -f ${COMPOSE_FILE} run --rm --no-deps \
		--publish 80:80 \
		certbot \
		certbot certonly \
		--domains ${LETSENCRYPT_DOMAINS} \
		--email ${LETSENCRYPT_EMAIL} \
		--agree-tos \
		--no-eff-email \
		--standalone \
		--dry-run

# Issue staging SSL certificates according to the environment variables
ssl.cert.staging:
	docker compose -f ${COMPOSE_FILE} run --rm --no-deps \
		--publish 80:80 \
		certbot \
		certbot certonly \
		--domains ${LETSENCRYPT_DOMAINS} \
		--email ${LETSENCRYPT_EMAIL} \
		--agree-tos \
		--no-eff-email \
		--standalone \
		--staging

# Generate a 2048-bit DH parameter file
ssl.dh:
	sudo openssl dhparam -out ./.docker/${IMAGE_REGISTRY}/nginx/ssl/dhparam.pem 2048

# Show the list of registered certificates
ssl.ls:
	docker compose -f ${COMPOSE_FILE} run --rm --entrypoint "certbot certificates" certbot


#-----------------------------------------------------------
# Common back
#-----------------------------------------------------------

# Artisan optimize
optimize.%:
	docker compose -f ${COMPOSE_FILE} exec $* php artisan optimize

# Copy laravel source files to container
copy.laravel.%:
	docker cp ./src/api/. $*:/var/www/html/

#
copy.uploads.%:
	docker cp ./src/api/public/uploads/. $*:/var/www/html/public/uploads/

create_dirs.%:
	docker compose -f ${COMPOSE_FILE} exec $* mkdir /var/www/html/storage/framework/sessions
#-----------------------------------------------------------
# Dashboard
#-----------------------------------------------------------



#-----------------------------------------------------------
# Api
#-----------------------------------------------------------

# Init variables for development environment
env.api.dev:
	cp ./src/api/.env.dev ./src/api/.env

# Init variables for production environment
env.api.prod:
	cp ./src/api/.env.prod ./src/api/.env

# Init variables for stage environment
env.api.stage:
	cp ./src/api/.env.stage ./src/api/.env

# Run the tinker service
tinker:
	docker compose -f ${COMPOSE_FILE} exec api php artisan tinker

# Clear the api cache
cache.clear:
	docker compose -f ${COMPOSE_FILE} exec api php artisan cache:clear

# Migrate the database
db.migrate:
	docker compose -f ${COMPOSE_FILE} exec api php artisan migrate

# Alias to migrate the database
migrate: db.migrate

# Rollback the database
db.rollback:
	docker compose -f ${COMPOSE_FILE} exec api php artisan migrate:rollback

# Seed the database
db.seed:
	docker compose -f ${COMPOSE_FILE} exec api php artisan db:seed

# Fresh the database state
db.fresh:
	docker compose -f ${COMPOSE_FILE} exec api php artisan migrate:fresh

# Refresh the database
db.refresh: db.fresh db.seed

# Install composer dependencies
composer.install:
	docker compose -f ${COMPOSE_FILE} exec api composer install

# Install composer dependencies from stopped containers
r.composer.install:
	docker compose -f ${COMPOSE_FILE} run --rm --no-deps api composer install

# Alias to install composer dependencies
ci: composer.install

# Update composer dependencies
composer.update:
	docker compose -f ${COMPOSE_FILE} exec api composer update

# Update composer dependencies from stopped containers
r.composer.update:
	docker compose -f ${COMPOSE_FILE} run --rm --no-deps api composer update

# Alias to update composer dependencies
cu: composer.update

# Show outdated composer dependencies
composer.outdated:
	docker compose -f ${COMPOSE_FILE} exec api composer outdated

# PHP composer autoload command
composer.autoload:
	docker compose -f ${COMPOSE_FILE} exec api composer dump-autoload

# Generate a symlink to the storage directory
storage.link:
	docker compose -f ${COMPOSE_FILE} exec api php artisan storage:link --relative

# Clear temps
remove.temps: 
	rm -rf ./storage/logs/*
	rm -rf ./storage/framework/sessions/*
	rm -rf ./storage/framework/views/*
	rm -rf ./storage/framework/cache/data/*

# Permissions
permissions: 
	chown -R www-data:www-data ./public
	chown -R www-data:www-data ./storage
	chown -R www-data:www-data ./storage_dashboard
	chmod -R 755 ./public
	chmod -R 755 ./storage	
	chmod -R 755 ./storage_dashboard

# Give permissions of the storage folder to the www-data
storage.public:
	chmod -R 755 ./storage
	chmod -R 755 ./public
	chown -R 0:0 ./storage
	chown -R 0:0 ./public

# Give permissions of the storage folder to the www-data
storage.perm:
	chmod -R 755 ./src/api/public
	chown -R www-data:www-data ./src/api/public

# Give permissions of the storage folder to the current user
storage.perm.me:
	chmod -R 755 ./src/api/public
	chown -R "$(shell id -u):$(shell id -g)" ./src/api/public

# Give files ownership to the current user
own.me:
	sudo chown -R "$(shell id -u):$(shell id -g)" ./src/api

# Reload the Octane workers
octane.reload:
	docker compose -f ${COMPOSE_FILE} exec api php artisan octane:reload

# Alias to reload the Octane workers
or: octane.reload


#-----------------------------------------------------------
# Queue
#-----------------------------------------------------------

# Restart the queue process
queue.restart:
	docker compose -f ${COMPOSE_FILE} exec queue php artisan queue:restart

#-----------------------------------------------------------
# Testing (only for development environment)
#-----------------------------------------------------------

# Run phpunit tests (requires 'phpunit/phpunit' composer package)
test:
	docker compose -f ${COMPOSE_FILE} exec api ./vendor/bin/phpunit --order-by=defects --stop-on-defect

# Alias to run phpunit tests
t: test

# Run phpunit tests with the coverage mode (TODO: install PCOV or other lib)
coverage:
	docker compose -f ${COMPOSE_FILE} exec api ./vendor/bin/phpunit --coverage-html ./.coverage

# Run dusk tests (requires 'laravel/dusk' composer package)
dusk:
	docker compose -f ${COMPOSE_FILE} exec api php artisan dusk

# Generate code metrics (requires 'phpmetrics/phpmetrics' composer package)
metrics:
	docker compose -f ${COMPOSE_FILE} exec api ./vendor/bin/phpmetrics --report-html=./.metrics api/app

#-----------------------------------------------------------
# Redis
#-----------------------------------------------------------

# Enter the redis container
redis:
	docker compose -f ${COMPOSE_FILE} exec redis redis-cli

# Flush the redis state
redis.flush:
	docker compose -f ${COMPOSE_FILE} exec redis redis-cli FLUSHALL

#-----------------------------------------------------------
# Swarm
#-----------------------------------------------------------

# Deploy the stack
swarm.deploy:
	docker stack deploy --compose-file ${COMPOSE_FILE} api

# Remove/stop the stack
swarm.rm:
	docker stack rm api

# List of stack services
swarm.services:
	docker stack services api

# List the tasks in the stack
swarm.ps:
	docker stack ps api

# Init the Docker Swarm Leader node
swarm.init:
	docker swarm init


#-----------------------------------------------------------
# Docker
#-----------------------------------------------------------

# Create shared gateway network
network.api:
	docker network create api

# Init variables for development environment
env.dev:
	cp .env.dev .env

# Init variables for production environment
env.prod:
	cp .env.prod .env

# Init variables for stage environment
env.stage:
	cp .env.stage .env


# Build and start containers
bupbus: bup yarn.build yarn.start 

# Build and start containers
bup: build.all up

# Start containers
up:
	docker compose -f ${COMPOSE_FILE} up -d

# Stop containers
down:
	docker compose -f ${COMPOSE_FILE} down --remove-orphans

# Build containers
build:
	docker compose -f ${COMPOSE_FILE} build

# Build all containers
build.all: build.base build

# Build the base api image
build.base:
	docker build --file ${BASE_IMAGE_DOCKERFILE} --tag ${IMAGE_REGISTRY}/api-base:${IMAGE_TAG} .

# Show list of running containers
ps:
	docker compose -f ${COMPOSE_FILE} ps

# Restart containers
restart:
	docker compose -f ${COMPOSE_FILE} restart

# Reboot containers
reboot: down up

# View output logs from containers
logs:
	docker compose -f ${COMPOSE_FILE} logs --tail 500

# Follow output logs from containers
logs.f:
	docker compose -f ${COMPOSE_FILE} logs --tail 500 -f

#-----------------------------------------------------------
# Danger zone
#-----------------------------------------------------------

# Prune stopped docker containers and dangling images
danger.prune:
	docker system prune

# Remove all app files and folders (leave only dockerized template)
danger.remove: down
	sudo rm -rf \
		.idea \
		.vscode \
		.vscode \
		app \
		bootstrap \
		config \
		database \
		lang \
		public \
		resources \
		routes \
		storage \
		vendor \
		tests \
		.editorconfig \
		.env \
		.env.example \
		.gitattributes \
		.gitignore \
		.phpunit.result.cache \
		.styleci.yml \
		artisan \
		composer.json \
		composer.lock \
		package.json \
		phpunit.xml \
		README.md \
		webpack.mix.js