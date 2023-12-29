FROM hanabosocom/php-base:php-8.3-alpine

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV PHP_FPM_MAX_REQUESTS 500
ENV PHP_FPM_MAX_CHILDREN 10

COPY ./ /var/www
COPY php-local.ini /usr/local/etc/php/conf.d/zz_local.ini

RUN mkdir -p /var/www/var/log /var/www/var/cache /var/lib/nginx/tmp/client_body && \
    chmod -R 774 /var/www/var /var/lib/nginx && \
    chown -R www-data /var/www/var /var/lib/nginx

WORKDIR /var/www

CMD [ "php-w-nginx.sh" ]
