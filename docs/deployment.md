# Deployment Guide — real_world_hero

## Production URL

https://realworldhero.davisco.dev

## Infrastructure

- VPS: Hetzner
- DNS: Cloudflare
- Deployment: Coolify
- Runtime: Docker + Docker Compose
- Reverse proxy: Coolify / Traefik
- Application stack:
  - Laravel
  - PHP-FPM
  - Nginx
  - MySQL

---

## Coolify Setup

### Project

`Production`

### Resource

`real_world_hero`

### Source

- GitHub App
- Repository: `davisco82/real_world_hero`
- Branch: `main`

### Build Pack

`Docker Compose`

### Compose file

```text
docker-compose.prod.yml
Domain Routing

The domain must be assigned to the nginx service, not the app service.

Correct:

Domains for nginx:
https://realworldhero.davisco.dev

Wrong:

Domains for app:
https://realworldhero.davisco.dev

Reason:

nginx listens on HTTP port 80
app is PHP-FPM on port 9000
Traefik/Coolify must route public traffic to Nginx, not PHP-FPM
Production Docker Files

Required files:

docker-compose.prod.yml
docker/php/Dockerfile
docker/nginx/Dockerfile
docker/nginx/default.conf
.dockerignore
Production Compose

The production compose should run only:

app
nginx
mysql

Do not run these in production until explicitly needed:

node
queue
horizon
redis

Node/Vite should not run as a long-running production container.

Environment Variables

Each variable must be added separately in Coolify.

Required production variables:

APP_NAME=RealWorldHero
APP_ENV=production
APP_DEBUG=false
APP_URL=https://realworldhero.davisco.dev
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=secret

MYSQL_DATABASE=app
MYSQL_USER=app
MYSQL_PASSWORD=secret
MYSQL_ROOT_PASSWORD=root

UID=1000
GID=1000

Coolify may also automatically create:

SERVICE_URL_APP=https://realworldhero.davisco.dev
SERVICE_FQDN_APP=realworldhero.davisco.dev

These can stay.

APP_KEY

Generate APP_KEY inside the app container:

docker exec -it APP_CONTAINER_NAME sh -c "php artisan key:generate --show"

Add the generated value to Coolify:

APP_KEY=base64:...

Then redeploy.

Database

Laravel uses:

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=secret

Important:

DB_HOST=mysql

Do not use:

localhost
127.0.0.1

because Laravel runs inside a Docker container.

Migrations

After first successful deployment, run:

docker exec -it APP_CONTAINER_NAME sh -c "php artisan migrate --force"

This is required for tables such as:

sessions
cache
users

If sessions table is missing, Laravel may return:

500 Internal Server Error
Table 'app.sessions' doesn't exist
Health Check

From the server:

curl -I http://127.0.0.1:8111

Expected result:

HTTP/1.1 302 Found
Location: /login

This means:

Nginx works
PHP-FPM works
Laravel works
MySQL works
routing inside Docker works
Useful Docker Commands

List project containers:

docker ps -a | grep batqfg17

Check app container files:

docker exec -it APP_CONTAINER_NAME sh -c "ls -la /var/www/html | head -30"

Check Laravel status:

docker exec -it APP_CONTAINER_NAME sh -c "php artisan about"

Check Laravel logs:

docker exec -it APP_CONTAINER_NAME sh -c "tail -n 120 storage/logs/laravel.log"

Check Nginx logs:

docker logs NGINX_CONTAINER_NAME --tail 80

Check app logs:

docker logs APP_CONTAINER_NAME --tail 80

Check Nginx config inside container:

docker exec -it NGINX_CONTAINER_NAME sh -c "cat /etc/nginx/conf.d/default.conf"

Test Nginx → PHP-FPM connectivity:

docker exec -it NGINX_CONTAINER_NAME sh -c "getent hosts app && nc -zv app 9000"
Common Issues
502 Bad Gateway on domain

Most common cause:

The domain is assigned to app instead of nginx.

Fix in Coolify:

Domains for nginx:
https://realworldhero.davisco.dev

Remove domain from:

Domains for app
Laravel files missing inside app container

Symptom:

/var/www/html contains only .env, README.md, docker/, docker-compose.yaml

Cause:

Production compose used bind mount:

- ./:/var/www/html

Fix:

Do not use project bind mounts in production.

The Laravel app must be copied into the image through:

COPY --chown=www-data:www-data . /var/www/html
Missing APP_KEY

Symptom:

No application encryption key has been specified.

Fix:

Generate key:

docker exec -it APP_CONTAINER_NAME sh -c "php artisan key:generate --show"

Add it to Coolify as:

APP_KEY=base64:...
Missing sessions table

Symptom:

Table 'app.sessions' doesn't exist

Fix:

docker exec -it APP_CONTAINER_NAME sh -c "php artisan migrate --force"
Nginx config missing

Symptom:

/etc/nginx/conf.d is empty

Fix:

Use a production Nginx image:

FROM nginx:1.27-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/html/public

Do not rely on runtime bind mounts for Nginx config in production.

Composer install fails during build

Use:

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts

Reason:

Laravel artisan scripts may require runtime environment variables that are not available during Docker build.

Deployment Workflow

Standard workflow:

git add .
git commit -m "your message"
git push

Coolify auto-deploys from GitHub.

If needed:

Coolify → Force Deploy / Rebuild without cache
Stable Production Checkpoint

After the first working deployment, tag the commit:

git tag v1-production-working
git push origin v1-production-working

This creates a safe rollback point.

Future Improvements

Add later, one by one:

Redis
Queue worker
Horizon
Scheduler
npm build inside Docker image
persistent storage volume
backup strategy
monitoring

Do not add all services at once.

Recommended order:

app + nginx + mysql
migrations
Redis
queue worker
Horizon
scheduler
production asset build