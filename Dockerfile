FROM ubuntu:20.04

ARG WWWGROUP

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# =====================================
# php extensions
# =====================================
RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 libcurl4-openssl-dev \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys E5267A6C \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C300EE8C \
    && echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu focal main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.2-fpm php8.2-cli php8.2-dev \
       php8.2-pgsql php8.2-sqlite3 php8.2-gd \
       php8.2-curl php8.2-memcached \
       php8.2-imap php8.2-mysql php8.2-mbstring \
       php8.2-xml php8.2-zip php8.2-bcmath php8.2-soap \
       php8.2-intl php8.2-readline \
       php8.2-msgpack php8.2-igbinary php8.2-ldap \
       php8.2-redis \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get install -y mysql-client \
    && apt-get install -y postgresql-client

# Nodejs \
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# OpenSwoole
 RUN apt install -y software-properties-common \
     && add-apt-repository ppa:openswoole/ppa -y
 RUN apt install -y php8.2-openswoole

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.2

# =====================================
# install redis
# =====================================
# RUN pecl install -o -f redis \
#     &&  rm -rf /tmp/pear \
#     &&  docker-php-ext-enable redis

# =====================================
# Install debug dependencies
# =====================================
RUN apt-get install git -y \
    && apt-get install vim -y \
    && apt-get install curl -y

# =====================================
# Supervisor
# =====================================
RUN apt install supervisor -y
COPY ./docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# =====================================
# Cleanup
# =====================================
RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# php --------------------------------
COPY docker/www.conf /etc/php/8.2/fpm/pool.d/www.conf

# =====================================
# Starting...
# =====================================
RUN chmod +x /etc/supervisor/conf.d/supervisord.conf

# =====================================
# Build app
# =====================================
COPY . /var/www/html
RUN npm install
RUN npm run build
RUN composer install --no-interaction
RUN rm -rf .env \
    && cp .env.example .env \
    && php artisan key:generate
RUN rm -f database/database.sqlite \
    && touch database/database.sqlite
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/database
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/public
RUN php artisan migrate:fresh --seed

# Start supervisor
CMD ["/usr/bin/supervisord"]
