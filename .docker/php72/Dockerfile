FROM magento/magento-cloud-docker-php:7.2-cli-1.3.5

RUN set -eux; \
    printf 'deb [check-valid-until=no] http://archive.debian.org/debian buster main\n' > /etc/apt/sources.list; \
    printf 'deb [check-valid-until=no] http://archive.debian.org/debian-security buster/updates main\n' >> /etc/apt/sources.list; \
    apt-get -o Acquire::Check-Valid-Until=false update; \
    apt-get install -y --no-install-recommends autoconf g++ make pkg-config re2c; \
    pecl channel-update pecl.php.net; \
    pecl install xdebug-3.1.6; \
    rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xsl.ini; \
    echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20170718/xdebug.so" > /usr/local/etc/php/conf.d/99-xdebug.ini; \
    echo "xdebug.mode=debug,develop" >> /usr/local/etc/php/conf.d/99-xdebug.ini; \
    echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/99-xdebug.ini; \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/99-xdebug.ini; \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/99-xdebug.ini; \
    apt-get remove -y autoconf g++ make pkg-config re2c; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/pear