FROM debian:jessie

MAINTAINER Thorben Fohlmeister <thorben@fohlmeister.com>

RUN apt-get -q update && \
  apt-get install -qy --force-yes \
    apache2 libapache2-mod-php5 php5-sqlite php5-cli php5-mcrypt openssl ca-certificates && \
  apt-get -q clean && \
  rm -rf /var/lib/apt/lists/*

COPY source /source
COPY openssl.cnf /source/openssl.cnf
RUN chown -R www-data:www-data /source

# setup apache2
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite && \
  rm -rf /var/www && \
  ln -fs /source/public /var/www && \
  service apache2 restart

# database and certs get stored here
RUN mkdir -p /data

# setup dependencies & software
WORKDIR /source
RUN php -r 'copy("https://getcomposer.org/installer", "composer-setup.php");' && \
  php composer-setup.php && \
  rm composer-setup.php && \
  php composer.phar install -n

COPY run.sh /run.sh
RUN chmod +x /run.sh

VOLUME ["/data"]
EXPOSE 80
CMD ["/run.sh"]
