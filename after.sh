#!/usr/bin/env bash

apt-get update
apt-get install -y sphinxsearch
apt-get install -y hunspell-hu
apt-get install -y imagemagick
mysql -u homestead -psecret < /home/vagrant/szentiras-hu/tmp/database.sql
export SZENTIRAS_WEBAPP_ENVIRONMENT=local
cd /home/vagrant/szentiras-hu
./install.sh
