#!/usr/bin/env bash

apt-get update
apt-get install -y sphinxsearch
apt-get install -y hunspell-hu
apt-get install -y imagemagick
mysql -u homestead -psecret < /home/vagrant/szentiras-hu/tmp/database.sql
export SZENTIRAS_WEBAPP_ENVIRONMENT=local
cd /home/vagrant/szentiras-hu
./install.sh

echo '[mysqld]' > /etc/mysql/conf.d/szentiras-hu.cnf
echo 'query_cache_type=1' >> /etc/mysql/conf.d/szentiras-hu.cnf
service mysql restart

cp ./deploy/local/sphinx/sphinx.conf /etc/sphinxsearch
./deploy/local/sphinx/reindex.sh

echo 'START=yes' > /etc/default/sphinxsearch
service sphinxsearch start