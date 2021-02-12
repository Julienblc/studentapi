#!/bin/sh

# install dependencies
composer install
# migrate db
php bin/console doctrine:migrations:migrate

# run php
php-fpm