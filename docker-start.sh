#!/bin/bash

echo "=========================================="
echo "Symfony Forms - Docker Quick Start"
echo "=========================================="
echo ""

# Проверка Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен. Пожалуйста, установите Docker."
    exit 1
fi

# Проверка Docker Compose
if ! command -v docker compose &> /dev/null; then
    echo "❌ Docker Compose не установлен. Пожалуйста, установите Docker Compose."
    exit 1
fi

echo "✅ Docker и Docker Compose установлены"
echo ""

# Остановка предыдущих контейнеров
echo "🛑 Остановка предыдущих контейнеров..."
docker compose down

# Сборка и запуск контейнеров
echo "🏗️  Сборка и запуск контейнеров..."
docker compose up -d --build

# Ожидание готовности MySQL
echo "⏳ Ожидание готовности MySQL..."
sleep 10

# Выполнение миграций
echo "🗄️  Создание структуры базы данных..."
docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction || true

# Создание тестового пользователя (если еще не существует)
echo "👤 Создание тестового пользователя..."
docker compose exec -T app php -r "
require __DIR__ . '/vendor/autoload.php';
use App\Entity\User;

\$kernel = new \App\Kernel('prod', false);
\$kernel->boot();
\$container = \$kernel->getContainer();
\$em = \$container->get('doctrine')->getManager();

if (!\$em->getRepository(User::class)->findOneBy(['email' => 'test@example.com'])) {
    \$user = new User();
    \$user->setName('Test User');
    \$user->setEmail('test@example.com');
    \$user->setPhone('+79001234567');
    \$user->setPassword(password_hash('password123', PASSWORD_DEFAULT));
    \$em->persist(\$user);
    \$em->flush();
    echo 'Тестовый пользователь создан (test@example.com / password123)' . PHP_EOL;
} else {
    echo 'Тестовый пользователь уже существует' . PHP_EOL;
}
" || true

# Очистка кэша
echo "🧹 Очистка кэша..."
docker compose exec -T app php bin/console cache:clear

echo ""
echo "=========================================="
echo "✅ Приложение успешно запущено!"
echo "=========================================="
echo ""
echo "📍 URL: http://localhost:8050"
echo ""
echo "Полезные команды:"
echo "  - Просмотр логов: docker compose logs -f"
echo "  - Остановка: docker compose down"
echo "  - Перезапуск: docker compose restart"
echo "  - Вход в контейнер: docker compose exec app bash"
echo ""