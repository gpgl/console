#!/bin/bash

nohup php /code/server/artisan serve > server.log &
./vendor/bin/phpunit
