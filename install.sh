#!/bin/sh
npm install
php composer.phar install
php artisan migrate
gulp default