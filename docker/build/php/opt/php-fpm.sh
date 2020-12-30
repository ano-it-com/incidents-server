#!/bin/sh

/opt/wait-for-it.sh -s -t 0 ${POSTGRES_HOST}:${POSTGRES_PORT} -- \
/opt/wait-for-it.sh -s -t 0 ${RABBITMQ_HOST}:${RABBITMQ_PORT} -- \
sudo -EH -u www-data bash -c "
  php bin/console doctrine:migrations:migrate --no-interaction && \
  php bin/console assets:install public && \
  php bin/console cache:clear
" && \
php-fpm