# php:5.6.35-fpm-with-mongodb-alpine3.4
# docker build -t php:5.6.35-fpm-with-mongodb-alpine3.4 -f ./PHP5.6.35WithMongoDB.Dockerfile .
FROM php:5.6.35-fpm-alpine3.4

ARG SWAN_DOCKER_PHP_FPM_USER
ARG SWAN_DOCKER_PHP_FPM_UID

RUN set -x && \
    addgroup -g ${SWAN_DOCKER_PHP_FPM_UID} -S ${SWAN_DOCKER_PHP_FPM_USER} && \
    adduser -u ${SWAN_DOCKER_PHP_FPM_UID} -D -S -G ${SWAN_DOCKER_PHP_FPM_USER} ${SWAN_DOCKER_PHP_FPM_USER} && \
    apk add --no-cache openssl-dev && \
    apk add --no-cache --virtual .build-deps g++ make autoconf && \
	pecl install mongodb-1.4.2 && \
	docker-php-ext-enable mongodb && \
	apk del --purge .build-deps g++ make autoconf