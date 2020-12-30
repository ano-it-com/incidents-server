#!/usr/bin/env bash

bash -c " \
	groupmod --non-unique --gid $1 www-data && \
	usermod --non-unique --uid $2 www-data \
"

docker-php-ext-enable xdebug

bash /opt/entrypoint.sh
