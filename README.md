
# Book Seller — Yii2 Advanced

Веб-приложение для управления каталогом книг с системой подписок и SMS-уведомлениями через RabbitMQ.

##  Технологический стек

- **Backend:** PHP 8.x, Yii2 Advanced
- **База данных:** MariaDB 10.11
- **Веб-сервер:** Nginx (Alpine)
- **Очередь сообщений:** RabbitMQ 3.12
- **SMS-шлюз:** SMS Pilot API
- **Контейнеризация:** Docker + Docker Compose

---

## 📁 Структура проекта

```
book-seller/
── app/                          # Исходный код приложения (Yii2 Advanced)
│   ├── common/                   # Общий код для frontend и backend
│   │   ├── config/               # Общие конфигурации
│   │   │   ── params.php        # Параметры (RabbitMQ, SMS Pilot)
│   │   ├── helpers/              # Вспомогательные классы
│   │   │   └── ImageHelper.php   # Работа с загрузкой изображений
│   │   ├── models/               # ActiveRecord модели
│   │   │   ├── Author.php        # Модель автора
│   │   │   ├── Book.php          # Модель книги
│   │   │   ├── Subscription.php  # Модель подписки
│   │   │   ── User.php          # Модель пользователя
│   │   └── services/             # Бизнес-логика
│   │       ├── AuthorService.php # CRUD авторов
│   │       ├── BookService.php   # CRUD книг + триггер SMS
│   │       ├── SmsProducer.php   # Producer для RabbitMQ
│   │       └── SmsSender.php     # Отправка SMS через API
│   │
│   ├── console/                  # Консольное приложение
│   │   └── controllers/
│   │       └── SmsController.php # Consumer RabbitMQ (yii sms/consume)
│   │
│   ├── backend/                  # Админ-панель (порт 9081)
│   │   ├── controllers/          # Контроллеры админки
│   │   │   ├── AuthorController.php
│   │   │   └── BookController.php
│   │   ├── views/                # Представления админки
│   │   │   ├── author/
│   │   │   └── book/
│   │   └── web/                  # Публичная директория backend
│   │
│   ├── frontend/                 # Публичная часть (порт 9080)
│   │   ├── controllers/
│   │   │   ├── AuthorController.php  # + actionSubscribe
│   │   │   ├── BookController.php
│   │   │   ── SiteController.php    # Главная (ТОП-10), Login, Signup
│   │   ├── views/
│   │   │   ├── author/
│   │   │   ├── book/
│   │   │   ├── layouts/
│   │   │   │   └── _header.php       # Меню навигации
│   │   │   └── site/
│   │   │       └── index.php         # Главная с ТОП-10 авторов
│   │   └── web/                      # Публичная директория frontend
│   │
│   ── uploads/                  # Общая папка загрузок (images)
│       ├── author/               # Фото авторов
│       └── book/                 # Обложки книг
│
├── docker/                       # Docker-конфигурация
│   ├── docker-compose.yml        # Оркестрация сервисов
│   ├── nginx/
│   │   └── conf.d/
│   │       ├── frontend.conf     # Nginx для frontend (порт 80 → 9080)
│   │       └── backend.conf      # Nginx для backend (порт 81 → 9081)
│   ── php/
│       └── Dockerfile            # Образ PHP с расширениями
│
└── README.md                     # Этот файл
```

---

## 🐳 Docker-сервисы

| Сервис | Образ | Порты | Описание |
|--------|-------|-------|----------|
| `nginx` | nginx:alpine | 9080, 9081 | Веб-сервер для frontend и backend |
| `php` | custom (php-fpm) | — | PHP-FPM для обработки запросов |
| `sms-consumer` | custom (php-cli) | — | Worker RabbitMQ (yii sms/consume) |
| `mariadb` | mariadb:10.11 | 3307 | База данных |
| `rabbitmq` | rabbitmq:3.12-management-alpine | 5673, 15673 | Очередь сообщений + UI |

---

## 🔧 Запуск проекта

### 1. Клонирование и подготовка

```bash
git clone <repo-url> book-seller
cd book-seller/docker
```

### 2. Сборка и запуск

```bash
docker compose up -d --build
```

### 3. Применение миграций

```bash
docker compose exec php php yii migrate
```

### 4. Установка зависимостей (если нужно)

```bash
docker compose exec php composer install
docker compose exec php composer require php-amqplib/php-amqplib
```

---

## 🌐 Доступ к сервисам

| Сервис | URL | Логин / Пароль |
|--------|-----|----------------|
| **Frontend** | http://localhost:9080 | — |
| **Backend** | http://localhost:9081 | — |
| **RabbitMQ UI** | http://localhost:15673 | guest / guest |
| **MariaDB** | localhost:3307 | root / root |

---

## 📨 Как работает отправка SMS

```
┌─────────────┐         ┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│  Backend    │         │  Frontend    │         │  RabbitMQ    │         │  SMS Pilot   │
│  (создание  │         │  (подписка)  │         │  (очередь)   │         │  (API)       │
│  книги)     │         │              │         │              │         │              │
└──────┬──────┘         └──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                       │                        │                        │
       │  SmsProducer          │  SmsProducer           │  SmsConsumer           │  SmsSender
       │  notifyAboutNewBook() │  notifyAboutNewSub()   │  yii sms/consume       │  curl → API
       │                       │                        │                        │
       └───────────────────────┴────────────────────────┴────────────────────────┘
```

### События в очереди

1. **`new_book`** — при создании книги в админке. Отправляется всем подписчикам авторов книги.
2. **`new_subscription`** — при новой подписке на автора. Приветственное SMS.

### Запуск consumer вручную (для отладки)

```bash
docker compose exec php php yii sms/consume
```

---

## 📋 Основные эндпоинты

### Frontend (9080)

| URL | Описание |
|-----|----------|
| `/site/index` | Главная страница с ТОП-10 авторов года |
| `/book/index` | Каталог книг с поиском и фильтром по году |
| `/book/view?id=N` | Страница книги |
| `/author/index` | Список авторов |
| `/author/view?id=N` | Страница автора + подписка |
| `/author/subscribe?id=N` | POST — оформить подписку |
| `/site/signup` | Регистрация |
| `/site/login` | Вход |

### Backend (9081)

| URL | Описание |
|-----|----------|
| `/author/index` | CRUD авторов |
| `/book/index` | CRUD книг |

---

## ⚙️ Конфигурация

### Переменные окружения (`docker-compose.yml`)

```yaml
DB_HOST=mariadb
DB_NAME=yii2advanced
DB_USER=root
DB_PASSWORD=root
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
```

### Параметры приложения (`app/common/config/params.php`)

```php
'rabbitmq' => [
    'host' => 'rabbitmq',
    'port' => 5672,
    'user' => 'guest',
    'password' => 'guest',
],
'smspilot' => [
    'api_key' => 'YOUR_API_KEY',  // Получить на smspilot.ru
    'sender_name' => 'BookSeller',
],
```

---

## ️ Загрузка изображений

- **Хранилище:** `/var/www/app/uploads/` (общая папка для frontend и backend)
- **Структура:** `uploads/{entity}/{XX}/{YY}/{id}/original.{ext}`
- **Пример:** `uploads/book/00/03/5/original.jpg`
- **Thumbnail:** генерируется автоматически (300×300, качество 80)
- **Nginx:** отдаёт файлы через `location /uploads/` с кэшем 365 дней

---

## 🧪 Тестирование SMS

Для разработки можно использовать виртуальный ключ — SMS не будут отправляться, но запишутся в лог:

```php
// app/common/config/params.php
'smspilot' => [
    'api_key' => 'TEST_KEY',  // Виртуальный режим
    'sender_name' => 'BookSeller',
],
```

Логи: `app/runtime/logs/app.log` (категория `sms`).

---

## 📝 Лицензия

MIT