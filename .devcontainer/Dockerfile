FROM mcr.microsoft.com/devcontainers/php:8.5-bookworm

# The official PHP devcontainer image ships Xdebug; fail the build if that
# coverage prerequisite ever disappears from the base image.
RUN php -m | grep -qi '^xdebug$'

COPY tools/qa/composer.json tools/qa/composer.lock /opt/qa/
COPY tools/bench/composer.json tools/bench/composer.lock /opt/bench/

RUN composer install \
        --working-dir=/opt/qa \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --classmap-authoritative \
    && composer install \
        --working-dir=/opt/bench \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --classmap-authoritative \
    && chown -R vscode:vscode /opt/qa /opt/bench

ENV PATH="/opt/qa/vendor/bin:/opt/bench/vendor/bin:${PATH}"
