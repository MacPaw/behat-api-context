ARG from_image

FROM $from_image

ENV XDEBUG_MODE=off

RUN apk add --no-cache bash linux-headers $PHPIZE_DEPS \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && curl --silent https://getcomposer.org/composer-stable.phar -o /usr/bin/composer && chmod a+x /usr/bin/composer
