FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get install -y apache2 php8.3 libapache2-mod-php8.3 && \
    a2dismod mpm_event && \
    a2enmod mpm_prefork && \
    a2enmod php8.3 && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Supprimer le warning AH00558
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apachectl", "-D", "FOREGROUND"]
