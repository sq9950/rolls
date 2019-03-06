#!/bin/bash

## 系统更新
yum update

##写 mariadb repo
cat <<EOF > /etc/yum.repos.d/mariadb.repo
# MariaDB 10.2 RedHat repository list - created 2019-03-05 08:39 UTC
# http://downloads.mariadb.org/mariadb/repositories/
[mariadb]
name = MariaDB
baseurl = http://yum.mariadb.org/10.2/rhel7-amd64
gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB
gpgcheck=1
EOF

## epel repo
yum install epel-release
## webtatic repo
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

##缓存
yum clean all
yum makecache

# 装软件
yum install vim
yum install MariaDB-server MariaDB-client
yum install nginx memcached redis
yum install php72w-bcmath php72w-cli php72w-common php72w-fpm php72w-gd php72w-mbstring php72w-mysqlnd php72w-pdo php72w-opcache php72w-pecl-memcached php72w-pecl-redis

useradd www

mkdir -p /data/www/rolls
mkdir /etc/nginx/vhost
rm -rf /etc/nginx/nginx.conf        && cp ./nginx_conf/nginx.conf /etc/nginx/nginx.conf
rm -rf /etc/nginx/vhost/admin.conf  && cp ./nginx_conf/admin.conf /etc/nginx/vhost/admin.conf
rm -rf /etc/nginx/vhost/www.conf    && cp ./nginx_conf/www.conf   /etc/nginx/vhost/www.conf
rm -rf /etc/php-fpm.conf            && cp ./php/php-fpm.conf      /etc/php-fpm.conf
rm -rf /etc/php.ini                 && cp ./php/php.ini           /etc/php.ini
rm -rf /etc/php.d/www.conf          && cp ./php/www.conf          /etc/php.d/www.conf              

mysqladmin -uroot -p'' passwd admin
