#!/bin/bash

nohup php /code/server/artisan serve > ./tests/coverage/server.log 2>&1 &
./vendor/bin/phpunit
