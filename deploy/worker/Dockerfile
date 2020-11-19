FROM centos:8
RUN dnf -y update

RUN dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
RUN dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
RUN dnf -y module enable php:remi-7.4

RUN dnf -y install wget nginx mysql supervisor cronie php php-json php-bcmath php-gd php-zip php-mysql php-ffi

RUN mkdir /run/php-fpm
RUN mkdir /run/supervisor

RUN mkdir /var/log/app; chmod 777 /var/log/app
