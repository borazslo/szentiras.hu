FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y \
    libonig-dev \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

RUN apt install -y \
    hunspell \
    hunspell-hu

RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    mbstring \
    pdo pdo_mysql \
    exif pcntl bcmath gd zip xml

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

RUN a2enmod rewrite headers
COPY docker/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
