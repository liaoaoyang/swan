# php:7.2.4-fpm-with-mongodb-alpine3.7
# docker build -t php:7.2.4-fpm-with-mongodb-alpine3.7 -f ./PHP7.2.4WithMongoDB.Dockerfile .
FROM php:7.2.4-fpm-alpine3.7
RUN apk add --no-cache openssl-dev && \
    apk add --no-cache --virtual .build-deps g++ make autoconf && \
	pecl install mongodb-1.4.2 && \
	docker-php-ext-enable mongodb && \
	apk del --purge .build-deps g++ make autoconf