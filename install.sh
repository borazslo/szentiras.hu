#!/bin/sh
npm install
node_modules/.bin/bower --config.interactive=false install
node_modules/.bin/grunt
php composer.phar install
php artisan migrate
