# Symfony Forms Application

Приложение с двумя формами (регистрация и обратная связь) на Symfony 7 с использованием Docker Compose и MySQL.

## Требования

- Docker
- Docker Compose

## Структура проекта

```
forms-app/
├── src/
│   ├── Controller/
│   │   └── FormsController.php      # Контроллер форм
│   ├── Entity/
│   │   ├── User.php                 # Сущность пользователя
│   │   └── ContactMessage.php       # Сущность сообщения
│   ├── Form/
│   │   ├── RegistrationFormType.php # Форма регистрации
│   │   └── ContactFormType.php      # Форма обратной связи
│   └── Repository/
│       ├── UserRepository.php
│       └── ContactMessageRepository.php
├── templates/
│   ├── base.html.twig               # Базовый шаблон
│   └── forms/
│       └── index.html.twig          # Главная страница с формами
├── public/
│   ├── app.js                       # JavaScript логика
│   └── styles.css                   # Стили
├── docker compose.yml               # Docker Compose конфигурация
├── Dockerfile                       # Docker конфигурация
└── README.md                        # Этот файл
```

## Функциональность

### Форма регистрации
- Поля: имя, email, телефон, пароль, подтверждение пароля
- Валидация на frontend (JavaScript)
- Валидация на backend (Symfony Validator)
- Отправка без перезагрузки страницы (AJAX)
- Отображение зарегистрированных пользователей под формой

### Форма обратной связи
- Поля: email, сообщение
- Валидация на frontend (JavaScript)
- Валидация на backend (Symfony Validator)
- Отправка без перезагрузки страницы (AJAX)
- Отображение сообщений с именем пользователя (если зарегистрирован) или email

## Тестирование

### Запуск тестов через Docker

```bash
# Запуск всех тестов с автоматической сборкой
make test

# Или через скрипт
./docker-test.sh

# Или через docker compose напрямую
docker compose -f docker-compose.test.yml up --build --abort-on-container-exit
```

### Запуск тестов локально (без Docker)

```bash
# Установить зависимости для тестирования
composer install

# Запустить все тесты
./vendor/bin/phpunit

# Запустить с выводом детальной информации
./vendor/bin/phpunit --testdox

# Запустить только определенный тест
./vendor/bin/phpunit tests/Controller/FormsControllerTest.php
```

### Список тестов

- **Controller Tests** (tests/Controller/FormsControllerTest.php):
  - `testIndexPageLoads` - проверка загрузки главной страницы
  - `testRegistrationPageHasRequiredElements` - проверка элементов формы регистрации
  - `testContactPageHasRequiredElements` - проверка элементов формы обратной связи
  - `testRegistrationSuccess` - успешная регистрация
  - `testRegistrationValidationError` - проверка валидации регистрации
  - `testRegistrationPasswordMismatch` - проверка несовпадения паролей
  - `testContactMessageSuccess` - успешная отправка сообщения с зарегистрированным email
  - `testContactMessageWithUnknownEmail` - отправка сообщения с незарегистрированным email
  - `testContactMessageValidationError` - проверка валидации сообщения

- **Entity Tests**:
  - `tests/Entity/UserTest.php` - тесты сущности User
  - `tests/Entity/ContactMessageTest.php` - тесты сущности ContactMessage

### Покрытие кода

```bash
# Генерация отчета о покрытии кода
./vendor/bin/phpunit --coverage-html coverage

# Или с Clover XML
./vendor/bin/phpunit --coverage-clover coverage.xml
```

## Быстрый старт

### 1. Клонирование проекта (если нужно)
```bash
git clone <repository-url> <repository-url>
cd forms-app
```

### 2. Запуск через Docker Compose

```bash
# Запуск всех сервисов
docker compose up -d

# Просмотр логов
docker compose logs -f

# Остановка сервисов
docker compose down
```

Приложение будет доступно по адресу: **http://localhost:8050**

### 3. Инициализация базы данных (первый запуск)

```bash
# Выполнение миграций внутри контейнера
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

## Локальный запуск (без Docker)

Если вы хотите запустить проект локально без Docker:

### 1. Установка зависимостей

```bash
# Установка Composer (если не установлен)
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка PHP расширений (Debian/Ubuntu)
sudo apt-get install php php-cli php-xml php-curl php-mbstring php-mysql php-pdo php-json php-common

# Установка зависимостей проекта
composer install
```

### 2. Настройка базы данных MySQL

Создайте базу данных в MySQL:

```sql
CREATE DATABASE app_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'app'@'localhost' IDENTIFIED BY 'app_password';
GRANT ALL PRIVILEGES ON app_db.* TO 'app'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Настройка подключения к БД

Отредактируйте файл `.env`:

```env
DATABASE_URL="mysql://app:app_password@127.0.0.1:3306/app_db?serverVersion=8.0&charset=utf8mb4"
```

### 4. Создание структуры базы данных

```bash
# Создание миграций
php bin/console doctrine:migrations:generate

# Выполнение миграций
php bin/console doctrine:migrations:migrate --no-interaction
```

Или напрямую:

```bash
php bin/console doctrine:schema:create
```

### 5. Запуск встроенного сервера PHP

```bash
php -S localhost:8050 -t public
```

Приложение будет доступно по адресу: **http://localhost:8050**

## API Endpoints

### Регистрация
- **URL:** `/api/register`
- **Method:** `POST`
- **Content-Type:** `application/json`

**Request Body:**
```json
{
  "name": "Иван Иванов",
  "email": "ivan@example.com",
  "phone": "+79001234567",
  "password": "password123",
  "confirmPassword": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "user": {
    "name": "Иван Иванов",
    "email": "ivan@example.com",
    "phone": "+79001234567"
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "errors": {
    "email": "Введите корректный email",
    "password": "Пароль должен содержать минимум 6 символов"
  }
}
```

### Обратная связь
- **URL:** `/api/contact`
- **Method:** `POST`
- **Content-Type:** `application/json`

**Request Body:**
```json
{
  "email": "ivan@example.com",
  "message": "Текст сообщения (минимум 10 символов)"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": {
    "displayName": "Иван Иванов",
    "message": "Текст сообщения"
  }
}
```

## Полезные команды Docker

```bash
# Запуск сервисов
docker compose up -d

# Остановка сервисов
docker compose down

# Перезапуск сервисов
docker compose restart

# Просмотр логов
docker compose logs -f app
docker compose logs -f db

# Вход в контейнер приложения
docker compose exec app bash

# Выполнение команды внутри контейнера
docker compose exec app php bin/console cache:clear
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Удаление всех данных (внимание!)
docker compose down -v
```

## Полезные команды (Makefile)

```bash
# Показать все доступные команды
make help

# Запуск приложения
make start

# Остановка приложения
make stop

# Перезапуск приложения
make restart

# Запуск тестов
make test

# Быстрый запуск тестов
make test-quick

# Очистка всех контейнеров
make clean

# Просмотр логов
make logs

# Миграции базы данных
make db-migrate
make db-rollback

# Очистка кэша
make cache-clear

# Открыть консоль в контейнере
make console
```

## Полезные команды Symfony

```bash
# Очистка кэша
php bin/console cache:clear

# Просмотр маршрутов
php bin/console debug:router

# Создание миграции
php bin/console doctrine:migrations:generate

# Выполнение миграций
php bin/console doctrine:migrations:migrate

# Откат миграции
php bin/console doctrine:migrations:migrate prev

# Создание базы данных
php bin/console doctrine:database:create

# Создание схемы базы данных
php bin/console doctrine:schema:create

# Удаление схемы базы данных
php bin/console doctrine:schema:drop --force

# Просмотр состояния миграций
php bin/console doctrine:migrations:status
```

## Тестирование

### Тестирование форм

1. Откройте http://localhost:8050
2. Заполните форму регистрации:
   - Имя (минимум 2 символа)
   - Email (валидный email)
   - Телефон (минимум 10 цифр)
   - Пароль (минимум 6 символов)
   - Подтверждение пароля (должно совпадать)
3. Нажмите "Зарегистрироваться"
4. Данные пользователя появятся под формой

5. Заполните форму обратной связи:
   - Email (можно использовать email зарегистрированного пользователя)
   - Сообщение (минимум 10 символов)
6. Нажмите "Отправить"
7. Если email зарегистрирован, отобразится имя пользователя, иначе - email

### Тестовый пользователь

При запуске проекта через скрипт `docker-start.sh` автоматически создаётся тестовый пользователь:

- **Email:** `test@example.com`
- **Пароль:** `password123`

Пользователь добавляется только если в базе ещё нет записи с таким email.

### Тестирование API

```bash
# Регистрация
curl -X POST http://localhost:8050/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Тестовый Пользователь",
    "email": "test@example.com",
    "phone": "+79001234567",
    "password": "password123",
    "confirmPassword": "password123"
  }'

# Обратная связь
curl -X POST http://localhost:8050/api/contact \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "message": "Это тестовое сообщение"
  }'
```

## Структура базы данных

### Таблица users
```
id              INT AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(255) NOT NULL
email           VARCHAR(255) NOT NULL UNIQUE
phone           VARCHAR(20) NOT NULL
password        VARCHAR(255) NOT NULL -- хранит ХЭШ пароля, а не исходный текст
created_at      DATETIME NOT NULL
```

### Таблица contact_messages
```
id              INT AUTO_INCREMENT PRIMARY KEY
email           VARCHAR(255) NOT NULL
message         TEXT NOT NULL
created_at      DATETIME NOT NULL
```

## Требования к валидации

### Форма регистрации
- Имя: минимум 2 символа
- Email: валидный формат email
- Телефон: 10-15 цифр, может начинаться с +
- Пароль: минимум 6 символов
- Подтверждение пароля: должно совпадать с паролем

### Форма обратной связи
- Email: валидный формат email
- Сообщение: минимум 10 символов

## Технологии

- **Backend:** Symfony 7.4
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Database:** MySQL 8.0
- **ORM:** Doctrine ORM
- **Validation:** Symfony Validator
- **Containerization:** Docker, Docker Compose
- **Web Server:** Apache (в Docker), PHP Built-in Server (локально)

## Поддержка и разработка

Для развития проекта:

```bash
# Добавить новую зависимость
composer require package-name

# Обновить зависимости
composer update

# Создать новый контроллер
php bin/console make:controller ControllerName

# Создать новую сущность
php bin/console make:entity EntityName

# Создать новую форму
php bin/console make:form FormName
```

## Лицензия

MIT License