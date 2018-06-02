FROM debian:stretch

ARG UUID=1000
ARG GGID=1000
ARG USER=web

RUN if [ ! -z $(getent group $GGID) ] ; then groupmod -o -g 2019292 $(getent group $GGID | cut -d: -f1) ; fi && \
    addgroup --system --gid $GGID $USER && \
    if [ ! -z $(getent passwd $UUID) ] ; then usermod -o -u 2019292 $(getent passwd $UUID | cut -d: -f1) ; fi && \
    useradd -l --system --home-dir /var/cache/$USER  --shell /sbin/nologin --uid $UUID --gid $GGID $USER

RUN apt-get update && apt-get install -y wget apt-transport-https
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN sh -c 'echo "deb https://packages.sury.org/php/ stretch main" > /etc/apt/sources.list.d/php.list'

RUN apt-get update && apt-get install -y --force-yes \
    git \
    unzip \
    curl \
    php7.1-cli \
    php7.1-apcu \
    php7.1-curl \
    php7.1-fpm \
    php7.1-gd \
    php7.1-intl \
    php7.1-mbstring \
    php7.1-mcrypt \
    php7.1-zip \
    php7.1-mysql \
    php7.1-xml \
    php7.1-xdebug \
    gosu

RUN sed -i".back" s/\;date\.timezone\ \=.*/date\.timezone\ \=\ Europe\\/Paris/ /etc/php/7.1/fpm/php.ini && \
    sed -i".back" s/\;date\.timezone\ \=.*/date\.timezone\ \=\ Europe\\/Paris/ /etc/php/7.1/cli/php.ini && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    mkdir -p /var/run/php

ADD . /var/app

RUN mkdir -p /var/app/web/bundles /var/app/web/freelance /var/app/web/gallery /var/app/web/media /var/app/web/uploads /var/cache/web && \
    chown -R $USER. /var/app /var/cache/web

USER $USER
WORKDIR "/var/app"

EXPOSE 9000

USER root
CMD /usr/sbin/php-fpm7.1 --nodaemonize
