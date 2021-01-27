### PHP version we are targetting
ARG PHP_VERSION=7.2


### Placeholder for basic dev stage for use with docker-compose
FROM uogsoe/soe-php-apache:${PHP_VERSION} as dev

COPY docker/app-start docker/app-healthcheck /usr/local/bin/
RUN chmod u+x /usr/local/bin/app-start /usr/local/bin/app-healthcheck
CMD ["/usr/local/bin/app-start"]


### Prod php dependencies
FROM uogsoe/soe-php-apache:${PHP_VERSION} as prod-composer
ENV APP_ENV=production
ENV APP_DEBUG=0

WORKDIR /var/www/html

USER nobody

#- make paths that the laravel composer.json expects to exist
RUN mkdir -p database/seeds database/factories

COPY composer.* ./

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist


### QA php dependencies
FROM prod-composer as qa-composer
ENV APP_ENV=local
ENV APP_DEBUG=1

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist


### And build the prod app
FROM dev as prod

WORKDIR /var/www/html

ENV APP_ENV=production
ENV APP_DEBUG=0

#- Copy our start scripts and php/ldap configs in
COPY docker/ldap.conf /etc/ldap/ldap.conf
COPY docker/custom_php.ini /usr/local/etc/php/conf.d/custom_php.ini

#- Copy in our prod php dep's
COPY --from=prod-composer /var/www/html/vendor /var/www/html/vendor

#- Copy in our code
COPY . /var/www/html

#- Clear any cached composer stuff
RUN rm -fr /var/www/html/bootstrap/cache/*.php

#- If horizon is installed force it to rebuild it's public assets
RUN if grep -q horizon composer.json; then php /var/www/html/artisan horizon:assets; fi

#- Symlink the docker secret to the local .env so Laravel can see it
RUN ln -sf /run/secrets/.env /var/www/html/.env

#- Clean up and production-cache our apps settings/views/routing
RUN php /var/www/html/artisan storage:link && \
    php /var/www/html/artisan view:cache && \
    php /var/www/html/artisan route:cache && \
    chown -R www-data:www-data storage bootstrap/cache

#- Set up the default healthcheck
HEALTHCHECK --start-period=30s CMD /usr/local/bin/app-healthcheck

#- And off we go...
CMD ["/usr/local/bin/app-start"]


### Build the ci version of the app (prod+dev packages)
FROM prod as ci

ENV APP_ENV=local
ENV APP_DEBUG=1

#- Copy in our QA php dep's
COPY --from=qa-composer /var/www/html/vendor /var/www/html/vendor

#- Install sensiolabs security scanner and clear the caches
RUN curl -OL -o /usr/local/bin/phpcs https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar && \
    php /var/www/html/artisan view:clear && \
    php /var/www/html/artisan cache:clear
