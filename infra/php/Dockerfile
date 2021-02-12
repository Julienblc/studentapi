FROM php:7.3-fpm-alpine
RUN apk --update --no-cache add git
RUN set -ex \
  && apk --no-cache add \
    postgresql-dev
RUN docker-php-ext-install pdo pdo_pgsql
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

COPY entrypoint-php.sh /usr/bin/entrypoint-php
RUN chmod +x /usr/bin/entrypoint-php

ENTRYPOINT ["entrypoint-php"]

EXPOSE 9000