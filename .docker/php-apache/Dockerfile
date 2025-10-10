FROM php:8.4-apache
WORKDIR "/var/www"

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
	libpng-dev \
	libjpeg-dev \
	unzip \
    git \
	&& rm -rf /var/lib/apt/lists/*

# Install Xdebug
RUN yes | pecl install xdebug

RUN apt-get install -y zlib1g-dev \
	&& docker-php-ext-install pdo_mysql gd \
	&& rm -rf /var/lib/apt/lists/* \
	&& apt-get clean -y

RUN sed -i 's!ErrorLog ${APACHE_LOG_DIR}/error.log!ErrorLog /dev/stderr!g' /etc/apache2/apache2.conf
RUN echo 'ServerName _' >> /etc/apache2/apache2.conf
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && chmod +x composer.phar \
    && mv composer.phar /usr/local/bin/composer

RUN a2enmod rewrite

RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v3.phar -o php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/bin/php-cs-fixer
