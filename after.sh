#!/usr/bin/env bash

export DEBIAN_FRONTEND=noninteractive

add-apt-repository -y ppa:builds/sphinxsearch-rel22
apt-get update
apt-get install -y sphinxsearch
apt-get install -y hunspell
apt-get install -y hunspell-hu
apt-get install -y graphicsmagick
mysql -u homestead -psecret < /home/vagrant/szentiras-hu/tmp/database.sql
export APP_HOME=/home/vagrant/szentiras-hu


sudo -H -u vagrant bash -c "cd $APP_HOME; export SZENTIRAS_WEBAPP_ENVIRONMENT=local; ./install.sh"

echo '[mysqld]' > /etc/mysql/conf.d/szentiras-hu.cnf
echo 'query_cache_type=1' >> /etc/mysql/conf.d/szentiras-hu.cnf
service mysql restart

service sphinxsearch stop
cp $APP_HOME/deploy/local/sphinx/sphinx.conf /etc/sphinxsearch
indexer --all
service sphinxsearch start