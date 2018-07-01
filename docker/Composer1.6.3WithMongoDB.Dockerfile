# composer:1.6.3-with-mongodb
# docker build -t swan:composer1.6.3-with-mongodb -f ./Composer1.6.3WithMongoDB.Dockerfile .
FROM composer:1.6.3
RUN curl -s 'https://api.ip.la/en' | grep -q China && \
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories && apk update; \
    set -x && \
    apk add --no-cache openssl-dev && \
    apk add --no-cache --virtual .build-deps g++ make autoconf && \
	pecl install mongodb-1.4.2 && \
	docker-php-ext-enable mongodb && \
	apk del --purge .build-deps g++ make autoconf