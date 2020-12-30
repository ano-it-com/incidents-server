#!/usr/bin/env bash

sudo -EH -u www-data bash -c " \
  composer install --prefer-dist --ignore-platform-reqs --working-dir=${1} && \
  composer clearcache \
"