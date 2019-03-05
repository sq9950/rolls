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

#mysqladmin -uroot -p'' passwd admin


