#!/bin/bash

echo "=========================================="
echo "Symfony Forms - Run Tests"
echo "=========================================="
echo ""

# Проверка Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен. Пожалуйста, установите Docker."
    exit 1
fi

# Проверка Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose не установлен. Пожалуйста, установите Docker Compose."
    exit 1
fi

echo "✅ Docker и Docker Compose установлены"
echo ""

# Остановка предыдущих контейнеров
echo "🛑 Остановка предыдущих контейнеров..."
docker-compose -f docker-compose.test.yml down -v

# Запуск контейнеров с тестами
echo "🏗️  Запуск контейнеров с тестами..."
docker-compose -f docker-compose.test.yml up --build --abort-on-container-exit

# Проверка результата
RESULT=$?

echo ""
echo "=========================================="

if [ $RESULT -eq 0 ]; then
    echo "✅ Все тесты успешно пройдены!"
    echo "=========================================="
    echo ""
    
    # Спрашиваем, нужно ли остановить контейнеры
    read -p "Остановить контейнеры? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        docker-compose -f docker-compose.test.yml down -v
    fi
else
    echo "❌ Тесты не пройдены!"
    echo "=========================================="
    echo ""
    echo "Контейнеры оставлены в работе для отладки."
    echo "Для остановки выполните: docker-compose -f docker-compose.test.yml down -v"
    echo "Для просмотра логов: docker-compose -f docker-compose.test.yml logs -f app"
fi

exit $RESULT