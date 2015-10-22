#!/bin/sh
npm install
php composer.phar install
php artisan migrate
node_modules/.bin/gulp default
php artisan cache:clear
php artisan twig:clean