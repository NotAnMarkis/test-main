# deploy.ps1
Write-Host "=== Автоматизация задач для Laravel-проекта ==="

# Установка зависимостей
Write-Host "Установка зависимостей..."
composer install

# Запуск миграций с сидированием
Write-Host "Выполнение миграций..."
php artisan migrate:fresh --seed

# Запуск тестов
Write-Host "Запуск тестов..."
php artisan test

# Запуск локального сервера (опционально)
php artisan serve
