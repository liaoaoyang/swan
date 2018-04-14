# php:5.6.35-fpm-with-mongodb-alpine3.4
# docker build -t php:5.6.35-fpm-with-mongodb-alpine3.4 -f ./PHP5.6.35WithMongoDB.Dockerfile .
FROM php:5.6.35-fpm-alpine3.4
RUN apk add --no-cache openssl-dev && \
    apk add --no-cache --virtual .build-deps g++ make autoconf && \
	pecl install mongodb-1.4.2 && \
	docker-php-ext-enable mongodb && \
	apk del --purge .build-deps g++ make autoconf