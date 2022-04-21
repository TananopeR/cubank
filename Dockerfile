FROM php:apache AS build
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt update && apt -y upgrade \
    && apt install -y vim zip \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');"
FROM build AS development
COPY ./apache2.conf /etc/apache2/apache2.conf
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf