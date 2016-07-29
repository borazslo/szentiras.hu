#!/bin/sh
npm --no-bin-link install
php composer.phar install
php artisan migrate
node_modules/gulp/bin/gulp.js
php artisan cache:clear
php artisan twig:clean