# SWAN - Simple WeChat Alert Notifier

Based on [Laravel](https://laravel.com/) and [EasyWeChat](https://easywechat.org/).

User visit `http://swan.your-site.com/wechat/swan/mykey` to get `KEY` first, and you can send messages via API (via HTTP POST/GET)

```
http://swan.your-site.com/wechat/swan/KEY.send?text=YouText
```

SWAN also provides message detail page and keep details for some days.

```diff
- SWAN is still under develop and be careful when use it in production environments.
```

## Requirements

+ Nginx
+ PHP 5.6+
+ MySQL 5.6+/MongoDB 3+

## Project configuration

### Requirements

You may detect which database you are using and install the right requirements, so you should run:

```
composer detect-composer-json
```

first.

Example:

```
composer detect-composer-json
composer run-script post-root-package-install
composer update
```

### Nginx

```
server {
    server_name swan.sample.com;
    listen 80;
    index index.php index.html index.htm;
    root /data/www/swan.sample.com/public/;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php($|/){
        fastcgi_pass          127.0.0.1:9000;
        fastcgi_index         index.php;
        include               fastcgi_params;
        set $path_info        "";
        set $real_script_name $fastcgi_script_name;

        if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
            set $real_script_name $1;
            set $path_info        $2;
        }

        fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
        fastcgi_param SCRIPT_NAME     $real_script_name;
        fastcgi_param PATH_INFO       $path_info;
        fastcgi_param PHP_VALUE       open_basedir=/data/www/swan.sample.com/:$document_root:/tmp/:/proc/:/dev/urandom;
    }

    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {
        expires      30d;
    }

    location ~ .*\.(js|css)?$ {
        expires      12h;
    }

    access_log  /data/wwwlog/swan.sample.com/access.log access;
    error_log  /data/wwwlog/swan.sample.com/error.log error;
}

```

### Database

If you want to use MongoDB, you may config your .env with:

```
MONGO_DB_DSN=mongodb://username:password@host:port/database
MONGO_DB_DATABASE=swan
```

You can also set replica set and auth database like:

```
MONGO_DB_DSN=mongodb://username:password@host:port,username1:password1@host1:port1
MONGO_DB_DATABASE=swan
MONGO_DB_REPLICA_SET=xxset
MONGO_DB_AUTH_DATABASE=admin
```

## Operations

### Delete expired messages

In order to keep database smaller, SWAN only keep messages created in 30 days.

Use:

```
php artisan swan:clear-expired-messages
```

may help you to clear expired messages, they will deleted form database.

Configurations `SWAN_KEEP_MESSAGES_BEFORE_DAYS` and `SWAN_DELETE_MESSAGES_LIMIT_PER_TIME` in `.env` described how many days will you keep message and how many messages will you delete per loop.

Default configurations is:

```
SWAN_KEEP_MESSAGES_BEFORE_DAYS=30
SWAN_DELETE_MESSAGES_LIMIT_PER_TIME=100
```

### List WeChat users

```
php artisan swan:list-wechat-users
```

Will display users `key`/`WeChat Openid`/`Nickname`, you can add `page number` to view more users.

### Async message dispatch

Sometimes you want to dispatch asynchronous message, you need to keep

```
php artisan queue:work redis --queue=send_alert
```

running in the background and change your api from `key.send` to `key.async.send`.

### Custom WeChat menu

If you want to simplify WeChat custom menu you may use:

```
php artisan swan:setup-custom-menu
```

to make only key button in custom menu. It will only display a button with text `Key` by default. When user click it it will return the push KEY.

Read more of custom menu in WeChat [wiki](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141013).

## Docker

### Initialization

```
git clone https://github.com/liaoaoyang/swan
cd swan
docker build -t swan:composer1.6.3-with-mongodb -f ./docker/Composer1.6.3WithMongoDB.Dockerfile .
docker run --rm -v `pwd`:/app swan:composer1.6.3-with-mongodb run-script detect-composer-json
docker run --rm -v `pwd`:/app swan:composer1.6.3-with-mongodb run-script post-root-package-install
docker run --rm -v `pwd`:/app swan:composer1.6.3-with-mongodb update
# Setup .env
docker-compose -p swan -f `pwd`/docker/docker-compose.yml up -d
# Install dashboard, just run this once and do not run it until you change database
docker run --rm -v `pwd`:/var/www/html --network=swan_swan swan:7.2.4-fpm-with-mongodb-alpine3.7 php artisan admin:install
```

## Dashboard

Added [Laravel Admin](http://laravel-admin.org/) to provide a simple dashboard.

Messages:

![Messages](https://raw.githubusercontent.com/liaoaoyang/swan/master/public/img/readme/messages.jpg)

Users:

![Users](https://raw.githubusercontent.com/liaoaoyang/swan/master/public/img/readme/users.jpg)

Original username is `admin`, password is `SWAN_ADMIN_INIT_PASSWORD` in `.env`.

## Multiple WeChat OAuth callback domains

After OAuth login callback comes, we can get WeChat user information.

In order to get user information in other domains, SWAN provided [MyWXTAuth](https://github.com/liaoaoyang/swan/blob/master/app/Utils/MyWXTAuth.php)(My WeiXin Third-party Authentication).

[Sequence](https://github.com/liaoaoyang/swan/blob/master/public/img/readme/wxtauth.puml)：

```
     ,----.          ,---.             ,----.                      ,------.
     |User|          |T3P|             |SWAN|                      |WeChat|
     `-+--'          `-+-'             `-+--'                      `--+---'
       |               |                 |                            |    
       | ------------->|                 |                            |    
       |               |                 |                            |    
       |               |bid/url/key/scope|                            |    
       |               |----------------->                            |    
       |               |                 |                            |    
       |               |                 | OAuth callback url/code/...|    
       |               |                 | --------------------------->    
       |               |                 |                            |    
       |               |                 |                            |    
       | <-------------------------------------------------------------    
       |               |                 |                            |    
       |               |            confirm                           |    
       | ------------------------------------------------------------->    
       |               |                 |                            |    
       |               |                 |      user information      |    
       |               |                 | <---------------------------    
       |               |                 |                            |    
       |               |user information |                            |    
       |               |<-----------------                            |    
     ,-+--.          ,-+-.             ,-+--.                      ,--+---.
     |User|          |T3P|             |SWAN|                      |WeChat|
     `----'          `---'             `----'                      `------'

```


## TODO

+ Dashboard √
+ Async message dispatch √
+ Message rate control
+ Automatic deployment
+ Security
+ Monitor
+ WeChat templates adaptation
+ Documents

