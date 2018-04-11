# composer:1.6.3-with-mongodb
# docker build -t composer:1.6.3-with-mongodb -f ./Composer1.6.3WithMongoDB.Dockerfile .
FROM composer:1.6.3
RUN apk add --no-cache openssl-dev && \
    apk add --no-cache --virtual .build-deps \
    g++ make autoconf && \
	pecl install mongodb-1.4.2 && \
	docker-php-ext-enable mongodb && \
	apk del --purge .build-deps