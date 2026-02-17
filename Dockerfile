# Immagine base PHP con Apache
FROM php:8.2-apache

# Installa estensioni necessarie per MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita mod_rewrite
RUN a2enmod rewrite

# Installa msmtp per invio email tramite Mailpit
RUN apt-get update && apt-get install -y msmtp msmtp-mta mailutils

# Copia configurazione msmtp
RUN echo "host mailpit" > /etc/msmtprc \
 && echo "port 1025" >> /etc/msmtprc \
 && echo "tls off" >> /etc/msmtprc \
 && echo "auth off" >> /etc/msmtprc \
 && echo "from bibliotech@localhost" >> /etc/msmtprc \
 && chmod 600 /etc/msmtprc

# Configura PHP per usare msmtp
RUN echo "sendmail_path = \"/usr/bin/msmtp -t\"" > /usr/local/etc/php/conf.d/mailpit.ini

# Directory di lavoro
WORKDIR /var/www/html

# Espone porta 80
EXPOSE 80
