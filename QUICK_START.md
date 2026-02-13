# Быстрый старт

## 🚀 Запуск через Docker (рекомендуется)

### 1. Автоматический запуск

```bash
chmod +x docker-start.sh
./docker-start.sh
```

Приложение будет доступно по адресу: **http://localhost:8050**

### 2. Ручной запуск

```bash
# Запуск сервисов
docker compose up -d

# Создание структуры БД
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### 3. Остановка

```bash
./docker-stop.sh

# или
docker compose down
```

## 💻 Локальный запуск (без Docker)

### 1. Установка зависимостей

```bash
# Установка Composer (если нет)
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка зависимостей
composer install
```

### 2. Настройка MySQL

```sql
CREATE DATABASE app_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'app'@'localhost' IDENTIFIED BY 'app_password';
GRANT ALL PRIVILEGES ON app_db.* TO 'app'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Настройка подключения

Скопируйте `.env.example` в `.env` и настройте `DATABASE_URL`:

```bash
cp .env.example .env
```

### 4. Создание БД

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### 5. Запуск

```bash
php -S localhost:8050 -t public
```

Приложение будет доступно по адресу: **http://localhost:8050**

## 📝 Тестирование

### Форма регистрации
1. Откройте http://localhost:8050
2. Заполните все поля левой формы
3. Нажмите "Зарегистрироваться"
4. Данные появятся под формой

### Форма обратной связи
1. Заполните поля правой формы
2. Если email зарегистрирован - покажется имя, иначе - email
3. Нажмите "Отправить"
4. Сообщение появится под формой

## 🔍 Проверка через API

```bash
# Регистрация
curl -X POST http://localhost:8050/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Тест",
    "email": "test@test.com",
    "phone": "+79001234567",
    "password": "123456",
    "confirmPassword": "123456"
  }'

# Обратная связь
curl -X POST http://localhost:8050/api/contact \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@test.com",
    "message": "Тестовое сообщение"
  }'
```

## 📚 Подробная документация

Смотрите [README.md](README.md) для полной документации.