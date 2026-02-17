# Immagine base PHP con Apache
FROM php:8.2-apache

# Installa estensioni necessarie per MySQL (mysqli è quello che usi tu)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita mod_rewrite per Apache
RUN a2enmod rewrite

# Installa msmtp e le utility per le email
RUN apt-get update && apt-get install -y msmtp msmtp-mta mailutils && rm -rf /var/lib/apt/lists/*

# Copia configurazione msmtp (Aggiunto 'account default')
RUN echo "defaults" > /etc/msmtprc \
 && echo "auth off" >> /etc/msmtprc \
 && echo "tls off" >> /etc/msmtprc \
 && echo "account default" >> /etc/msmtprc \
 && echo "host mailpit" >> /etc/msmtprc \
 && echo "port 1025" >> /etc/msmtprc \
 && echo "from noreply@panettipitagora.edu.it" >> /etc/msmtprc \
 && chown www-data:www-data /etc/msmtprc \
 && chmod 600 /etc/msmtprc

# Configura PHP per usare msmtp
RUN echo "sendmail_path = \"/usr/bin/msmtp -t\"" > /usr/local/etc/php/conf.d/mailpit.ini

# Directory di lavoro
WORKDIR /var/www/html

# Espone porta 80
EXPOSE 80