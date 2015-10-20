#!/bin/sh
npm install
php composer.phar install
php artisan migrate --env=$SZENTIRAS_WEBAPP_ENVIRONMENT
