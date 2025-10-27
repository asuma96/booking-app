# Booking App

**Стек:** Laravel 12, PHP 8.2 (php-fpm), MySQL 8, Nginx, Inertia.js + Vue 3, Tailwind CSS, Docker

## Возможности

-Список услуг с разными длительностями
-Календарь с выбором недели
-Отображение доступных слотов на выбранный день
-Бронирование с указанием имени и телефона
-Защита от одновременного бронирования одного слота (race conditions)
-Валидация рабочего времени (10:00-20:00 МСК, Пн-Сб)
-Автоматический буфер 30 минут между бронированиями

## Требования

- **Linux / macOS / Windows (с WSL2)**
- **Docker Engine** 20.10+
- **Docker Compose** v2.0+
- Свободные порты: **8081** (web), **3307** (MySQL)

## Быстрый старт

### 1. Клонирование репозитория

```bash
git clone https://github.com/asuma96/booking-app
cd booking-app
```

### 2. Создание файлов окружения

```bash
# Копируем примеры конфигураций
cp .env.example .env
cp src/.env.example src/.env
```

По умолчанию используются следующие настройки:
- База данных: `booking_app`
- Пользователь БД: `app`
- Пароль БД: `app`

Вы можете изменить их в файле `.env` перед запуском.

### 3. Запуск контейнеров

```bash
docker compose up -d --build
```

При первом запуске:
- Установятся все зависимости Composer
- Сгенерируется ключ приложения (`APP_KEY`)
- Выполнятся миграции базы данных
- Заполнятся тестовые данные (seeding)

### 4. Открытие приложения

Откройте в браузере: **http://localhost:8081**

## Тестовые данные

После запуска автоматически создаются:

### Услуги

1. **Поездка на квадроцикле**
   - 30 минут
   - 60 минут

2. **Тур на эндуро**
   - 60 минут
   - 120 минут

### Занятые слоты

- **16 октября 2025**: 
  - Квадроцикл 30 мин: 13:00, 16:00
  - Квадроцикл 60 мин: 10:00
  - Эндуро 60 мин: 10:00, 11:30, 18:30

- **17 октября 2025**:
  - Квадроцикл 30 мин: 10:00, 11:00, 13:00, 18:00
  - Эндуро 120 мин: 14:00

## Управление контейнерами

```bash
# Просмотр логов
docker compose logs -f

# Остановка
docker compose stop

# Запуск
docker compose start

# Полное удаление (с базой данных)
docker compose down -v

# Перезапуск с пересборкой
docker compose down
docker compose up -d --build
```

## Работа с базой данных

### Подключение к MySQL

```bash
# Из хоста
mysql -h 127.0.0.1 -P 3307 -u app -papp booking_app

# Из контейнера app
docker compose exec app php artisan db
```

### Миграции и сиды

```bash
# Только миграции
docker compose exec app php artisan migrate

# Миграции + сиды
docker compose exec app php artisan migrate:fresh --seed
```

## Архитектура

Проект следует принципам **SOLID** и использует **чистую архитектуру**.

## Структура проекта

```
booking-app/
├── docker/                  # Docker конфигурация
├── src/                     # Laravel приложение
│   ├── app/
│   │   ├── Actions/         # Application Services
│   │   ├── Domain/          # Доменная логика (SOLID)
│   │   │   └── Booking/
│   │   │       ├── Contracts/       # Интерфейсы
│   │   │       ├── BookingValidator.php
│   │   │       ├── AvailabilityChecker.php
│   │   │       └── BookingRepository.php
│   │   ├── Http/
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── resources/
│   │   └── js/              # Vue компоненты
│   └── routes/
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Особенности реализации

### Обработка Race Conditions

Используется **pessimistic locking** на уровне базы данных:

1. При создании бронирования начинается транзакция
2. Создаётся/выбирается запись блокировки для конкретного дня (`day_locks`)
3. На запись устанавливается `FOR UPDATE` lock
4. Проверяется доступность слота
5. Создаётся бронирование
6. Commit освобождает блокировку

Это гарантирует, что два одновременных запроса на один день будут обработаны последовательно.

### Валидация бизнес-правил

-Бронирование только с 10:00 до 20:00 МСК
-Воскресенье — выходной
-️ Автоматический буфер +30 минут к каждой услуге
-Проверка, что конец с буфером укладывается в рабочее время
-Защита от пересечения с существующими бронированиями

## Разработка

### Установка зависимостей

```bash
# PHP
docker compose exec app composer install

# Node.js (если нужна пересборка фронтенда)
docker compose exec app npm install
docker compose exec app npm run build
```

### Запуск тестов

```bash
docker compose exec app php artisan test
```

## Troubleshooting

### Порт 8081 занят

Измените порт в `docker-compose.yml`:

```yaml
web:
  ports:
    - "9000:80"
```

### Ошибка подключения к БД

Проверьте статус контейнера MySQL:

```bash
docker compose ps
docker compose logs mysql
```

Подождите, пока MySQL полностью запустится (healthcheck).

### Ошибка прав доступа к storage

```bash
docker compose exec app chmod -R 777 storage bootstrap/cache
```

### APP_KEY не сгенерирован

```bash
docker compose exec app php artisan key:generate
```

## Лицензия

MIT

## Автор