#!/bin/sh

SWAN_ASYNC_SEND_QUEUE=$1

if [ -z $SWAN_ASYNC_SEND_QUEUE ]; then
    exit
fi

crond

if [ $? -ne 0 ]; then
    exit
fi

BASE_DIR=$(cd `dirname $0`; pwd)
BASE_DIR=`dirname $BASE_DIR`

if [ ! -f $BASE_DIR/artisan ];then
    exit
fi

cd $BASE_DIR

/usr/local/bin/php $BASE_DIR/artisan queue:work redis --queue=$SWAN_ASYNC_SEND_QUEUE