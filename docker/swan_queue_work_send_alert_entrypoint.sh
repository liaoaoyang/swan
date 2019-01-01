#!/bin/sh

SWAN_ASYNC_SEND_QUEUE=$1

if [ -z $SWAN_ASYNC_SEND_QUEUE ]; then
    exit
fi

crond

if [ $? -ne 0 ]; then
    exit
fi



/usr/local/bin/php /var/www/html/artisan queue:work redis --queue=$SWAN_ASYNC_SEND_QUEUE