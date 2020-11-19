FROM extraton/depool-worker:0.1.2

COPY ./ /var/www/html

COPY deploy/default.nginx.conf /etc/nginx/conf.d

RUN cd /var/www/html; \
    composer install --no-dev --no-interaction --no-cache; \
    composer dump-autoload -o; \
    composer dump-env prod; \
    rm .env
