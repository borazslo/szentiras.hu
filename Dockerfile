ARG NODE_VERSION=22-alpine
FROM node:${NODE_VERSION} AS node
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

FROM alpine:3.21 AS alpine
COPY --from=node /app .
RUN apk update && apk upgrade
RUN apk add php82
RUN apk add php82-ctype
RUN apk add php82-curl
RUN apk add php82-dom
RUN apk add php82-fileinfo
RUN apk add php82-iconv
RUN apk add php82-mbstring
RUN apk add php82-openssl
RUN apk add php82-pdo
RUN apk add php82-pdo_pgsql
RUN apk add php82-session
RUN apk add php82-tokenizer
RUN apk add php82-gd
RUN apk add php82-gmp
RUN apk add php82-phar
RUN apk add php82-simplexml
RUN apk add php82-xml
RUN apk add php82-xmlreader
RUN apk add php82-xmlwriter
RUN apk add php82-zip
RUN ln -s /usr/bin/php82 /usr/bin/php

# # Add extensions not included in the base image
# ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
# RUN install-php-extensions gd gmp pdo_pgsql zip

RUN php composer.phar install --no-dev --no-interaction
COPY . /var/www/html

# RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
# RUN a2enmod rewrite headers
# COPY docker/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
# COPY docker/docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
