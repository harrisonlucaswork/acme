#!/bin/bash

source ../.env

REL_VER=${REL_VER:-1.0}
PHP_VER=${PHP_VER:-8.2}

cd $(dirname "${BASH_SOURCE[0]}")
docker build -f app-php-$PHP_VER.dockerfile -t acme-app:php-$PHP_VER-$REL_VER "$@" .
