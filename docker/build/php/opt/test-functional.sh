#!/usr/bin/env bash

/opt/wait-for-it.sh -s -t 0 postgres:5432 -- \
sudo -EH -u www-data bash -c " \
  php bin/phpunit tests/Functional/
"