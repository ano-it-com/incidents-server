FROM php:7.4-fpm-alpine3.12

RUN apk add --no-cache \
    sudo \
    bash \
    nano \
    shadow \
    freetype \
    libxml2 \
    libzip \
    libsodium \
    libpq \
    icu-libs \
    postgresql-client \
    rabbitmq-c \
    zip \
    gzip && \
    apk add --update --no-cache --virtual .build $PHPIZE_DEPS \
        freetype-dev \
        libxml2-dev \
        libsodium-dev \
        oniguruma-dev \
        libzip-dev \
        postgresql-dev \
        rabbitmq-c-dev \
        icu-dev \
        g++ \
    && \
    docker-php-ext-install soap && \
    docker-php-ext-install zip && \
    docker-php-ext-install xmlrpc && \
    docker-php-ext-install intl && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install calendar && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install pcntl && \
    pecl install APCu && \
    pecl install redis-5.3.2 && \
    pecl install xdebug-2.9.8 && \
    pecl install amqp && \
    docker-php-ext-enable apcu && \
    docker-php-ext-enable redis && \
    docker-php-ext-enable intl && \
    docker-php-ext-enable amqp && \
    apk del .build \
    && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && \
    mkdir -p /var/www/html && \
    chown -R www-data:root /var/www; \
    chmod -R g=u /var/www

COPY docker/build/php/etc/*.ini $PHP_INI_DIR/conf.d/

COPY docker/build/php/etc/www.conf /usr/local/etc/php-fpm.d/zz-docker.conf

COPY docker/build/php/opt/*.sh /opt/

# Для кеширования в отдельный слой результата работы команды composer install
COPY --chown=www-data:www-data ./composer.json ./composer.lock /var/www/html/

RUN chmod +x /opt/*.sh && /opt/composer.sh /var/www/html/

COPY --chown=www-data:www-data . /var/www/html/

WORKDIR /var/www/html/

# Удаление всех файлов с переменными окружения, все переменные будут переданы при старте образа
RUN rm .env*

ENTRYPOINT ["/opt/entrypoint.sh"]

CMD ["/opt/php-fpm.sh"]
