#!/usr/bin/env bash

/opt/wait-for-it.sh -s -t 0 php:9000 -- \
sudo -EH -u www-data bash -c "php bin/console messenger:consume ${1} -n -vv"

