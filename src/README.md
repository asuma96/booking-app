# Booking App (Laravel + Inertia Vue)

Простое веб-приложение для бронирования услуг.  
**Стек:** Laravel 12, PHP 8.2 (php-fpm), MySQL 8, Nginx, Inertia + Vue 3, Docker.

## Быстрый старт (Linux)

```bash
# 0) внешняя сеть Docker (один раз на машине)
docker network ls | grep booking-app-net \
  || docker network create --subnet 10.225.0.0/16 booking-app-net

# 1) переменные для compose (рядом с docker-compose.yml)
cp .env.example .env || true
# по умолчанию:
# MYSQL_ROOT_PASSWORD=root
# DB_DATABASE=booking_app
# DB_USERNAME=app
# DB_PASSWORD=app

# 2) env Laravel
cp src/.env.example src/.env || true
# APP_URL=http://localhost:8081
# DB_HOST=mysql, DB_PORT=3306, DB_DATABASE/USER/PASSWORD как выше

# 3) старт
docker compose up -d --build

# открыть
# http://localhost:8081
