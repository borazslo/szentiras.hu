#!/usr/bin/env bash

PROVISIONED="/home/vagrant/szentiras-hu/PROVISIONED";

if [[ -f $PROVISIONED ]]; then
  echo "Skipping provisioning"
  exit
else
  echo "Provisioning"
  export DEBIAN_FRONTEND=noninteractive

  add-apt-repository -y ppa:builds/sphinxsearch-rel22
  apt-get update
  apt-get install -y sphinxsearch
  apt-get install -y hunspell
  apt-get install -y hunspell-hu
  apt-get install -y graphicsmagick
  apt-get install -y texlive
  apt-get install -y texlive-xetex
  apt-get install -y texlive-latex-extra
  apt-get install -y texlive-lang-hungarian
  apt-get install -y fonts-linuxlibertine
  mysql -u homestead -psecret < /home/vagrant/szentiras-hu/tmp/database.sql
  echo '[mysqld]' > /etc/mysql/conf.d/szentiras-hu.cnf
  echo 'query_cache_type=1' >> /etc/mysql/conf.d/szentiras-hu.cnf
  service mysql restart
  export APP_HOME=/home/vagrant/szentiras-hu
  sudo -H -u vagrant bash -c "cd $APP_HOME; export SZENTIRAS_WEBAPP_ENVIRONMENT=local; ./install.sh"

  service sphinxsearch stop
  cp $APP_HOME/deploy/local/sphinx/sphinx.conf /etc/sphinxsearch
  indexer --all
  service sphinxsearch start
  touch $PROVISIONED;
fi

