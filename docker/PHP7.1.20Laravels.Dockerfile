# php:7.1.20-laravels-alpine3.7
# docker build -t php:7.1.20-laravels-alpine3.7 -f ./docker/PHP7.1.20.laravels.Dockerfile .
FROM php:7.1.20-cli-alpine3.7

ARG SWAN_DOCKER_PHP_FPM_USER
ARG SWAN_DOCKER_PHP_FPM_UID

RUN curl -s 'https://api.ip.la/en' | grep -q China && \
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories && apk update; \
    set -x && \
    addgroup -g ${SWAN_DOCKER_PHP_FPM_UID:-500} -S ${SWAN_DOCKER_PHP_FPM_USER:-www} && \
    adduser -u ${SWAN_DOCKER_PHP_FPM_UID:-500} -D -S -G ${SWAN_DOCKER_PHP_FPM_USER:-www} ${SWAN_DOCKER_PHP_FPM_USER:-www} && \
    apk add --no-cache openssl-dev libstdc++ && \
    apk add --no-cache --virtual .build-deps g++ make autoconf && \
	docker-php-ext-install pdo_mysql shmop sysvshm zip && \
	pecl install inotify && \
	pecl install swoole && \
	docker-php-ext-enable inotify swoole && \
	apk del --purge .build-deps g++ make autoconf