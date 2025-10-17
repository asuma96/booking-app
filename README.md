# Booking App (Laravel + Inertia Vue)

## Требования
- Linux + Docker Engine + Docker Compose Plugin
- Порты: 8001 (web), 3307 (MySQL — опционально)

## Запуск
```bash
git clone <repo> && cd <repo>
# если файла .env (в корне) нет:
# echo -e "MYSQL_ROOT_PASSWORD=root\nDB_DATABASE=booking_app\nDB_USERNAME=app\nDB_PASSWORD=app" > .env
docker compose up -d --build
# открыть http://localhost:8001