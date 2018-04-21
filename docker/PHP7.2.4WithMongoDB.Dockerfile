# php:7.2.4-fpm-with-mongodb-alpine3.7
# docker build -t php:7.2.4-fpm-with-mongodb-alpine3.7 -f ./PHP7.2.4WithMongoDB.Dockerfile .
FROM php:7.2.4-fpm-alpine3.7

ARG SWAN_DOCKER_PHP_FPM_USER
ARG SWAN_DOCKER_PHP_FPM_UID

RUN set -x && \
    addgroup -g ${SWAN_DOCKER_PHP_FPM_UID} -S ${SWAN_DOCKER_PHP_FPM_USER} && \
    adduser -u ${SWAN_DOCKER_PHP_FPM_UID} -D -S -G ${SWAN_DOCKER_PHP_FPM_USER} ${SWAN_DOCKER_PHP_FPM_USER} && \
    apk add --no-cache --virtual .build-deps g++ make autoconf && \
	pecl install mongodb-1.4.2 && \
	docker-php-ext-enable mongodb && \
	apk del --purge .build-deps g++ make autoconf