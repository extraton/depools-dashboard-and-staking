FROM extraton/depool-worker:0.1

COPY ./ /var/www/html

RUN composer install --no-dev --no-interaction --no-cache \
    composer dump-autoload -o \
    composer dump-env prod

