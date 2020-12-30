ARG PHP_IMAGE

FROM ${PHP_IMAGE} as php

FROM nginx:mainline-alpine

RUN nginx -v

RUN apk add --no-cache bash && \
	adduser -u 82 -D -S -G www-data www-data

WORKDIR /var/www/html/

COPY ./etc/*.conf  /etc/nginx/conf.d/

COPY --from=php --chown=www-data:www-data /var/www/html/public /var/www/html/public

CMD ["nginx", "-g", "daemon off;"]