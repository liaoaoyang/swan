# swan:5.6.35-fpm-with-mongodb-alpine3.4
# docker build -t swan:5.6.35-fpm-with-mongodb-alpine3.4 -f ./PHP5.6.35WithMongoDB.Dockerfile .
FROM php:5.6.35-fpm-alpine3.4

ARG SWAN_DOCKER_PHP_FPM_USER
ARG SWAN_DOCKER_PHP_FPM_UID

RUN curl -s 'https://api.ip.la/en' | grep -q China && \
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories && apk update; \
    set -x && \
    addgroup -g ${SWAN_DOCKER_PHP_FPM_UID:-500} -S ${SWAN_DOCKER_PHP_FPM_USER:-www} && \
    adduser -u ${SWAN_DOCKER_PHP_FPM_UID:-500} -D -S -G ${SWAN_DOCKER_PHP_FPM_USER:-www} ${SWAN_DOCKER_PHP_FPM_USER:-www} && \
    apk add --no-cache openssl-dev && \
    apk add --no-cache --virtual .build-deps g++ make autoconf && \
	pecl install mongodb-1.2.9 && \
	docker-php-ext-enable mongodb && \
	docker-php-ext-install pdo_mysql && \
	apk del --purge .build-deps g++ make autoconf