#!/bin/sh
npm install
php composer.phar install
php artisan migrate
node_modules/.bin/gulp default