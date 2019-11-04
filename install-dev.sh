#!/bin/sh
nvm use v6.2.2
npm --no-bin-link install
php5.6 composer.phar install
php5.6 artisan migrate -n
node_modules/gulp/bin/gulp.js
php5.6 artisan cache:clear
php5.6 artisan twig:clean
