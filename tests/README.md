To run the tests you need to run the migrations and the seeders on an empty database.
```
php artisan migrate:refresh --env=testing
php artisan db:seed --env=testing
```

Then run the test:
```
php artisan test
```