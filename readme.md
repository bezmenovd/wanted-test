```bash
git clone https://github.com/bezmenovd/wanted-test.git .
cp .env.example .env
composer install
./vendor/bin/sail up
docker compose exec -t laravel php artisan key:generate
docker compose exec -t laravel php artisan migrate
docker compose exec -t laravel php artisan db:seed
```

Запросы в insomnia.json

Перед запуском повторного импорта желательно выполнить команду `php artisan app:clear-rows-table`


