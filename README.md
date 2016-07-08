chat-server
===============
Websocket chat base on Symfony use WAMP and Redis

Project bubbles. For use, need started bubbles backend.

Requirements
===========

```
PHP: >=7.0
Bubbles backend server
```

Install
============

```
$ git clone git@gitlab.sib-soft.ru:web/bubbles-messages.git
$ composer install -o
$ php app/console assets:install
```

Start server
============

```
$ php app/console gos:websocket:server
```

Deploy
======
In progress
```
cap [sandbox\production] deploy
```

Docker
=====
In progress

Troubleshooting
===============
