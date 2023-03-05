FROM composer:latest as builder
WORKDIR /build
COPY . .
RUN composer install --no-interaction --ignore-platform-reqs

FROM php:8.2-alpine
WORKDIR /app
COPY --from=builder /build .
RUN apk add --update supervisor autoconf build-base rabbitmq-c-dev \
    && pecl install amqp \
    && docker-php-ext-enable amqp
ENTRYPOINT ["supervisord", "--configuration", "messenger-worker.conf"]