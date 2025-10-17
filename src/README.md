# Booking App (Laravel + Inertia)

Простое приложение для бронирования услуг.  
**Стек:** Laravel 12, PHP 8.2 (php-fpm), MySQL 8, Nginx, Inertia + Vue 3, Docker.

## Быстрый старт

```bash
git clone https://github.com/asuma96/booking-app
cd booking-app

# внешняя сеть (один раз на машине)
docker network ls | grep booking-app-net \
  || docker network create --subnet 10.225.0.0/16 booking-app-net

cp .env.example .env
cp src/.env.example src/.env

docker compose up -d --build
# открыть http://localhost:8081
