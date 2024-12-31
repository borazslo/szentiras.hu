#!/bin/bash

cp /app/.env.example /app/.env

echo '[mysqld]' > /etc/mysql/conf.d/szentiras-hu.cnf
echo 'default_authentication_plugin=mysql_native_password' >> /etc/mysql/conf.d/szentiras-hu.cnf

service mysql start

mysql -e "CREATE USER 'homestead'@'localhost' IDENTIFIED BY 'secret';"
mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'homestead'@'localhost';"
mysql < /app/tmp/database.sql

nvm install v9.0.0
nvm use v9.0.0
npm --no-bin-link install

php composer.phar install
php artisan migrate -n
service mysql restart

node_modules/gulp/bin/gulp.js

php artisan cache:clear
php5.6 artisan twig:clean
