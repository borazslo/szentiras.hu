#!/usr/bin/env bash

PROVISIONED="/home/vagrant/szentiras-hu/PROVISIONED";

if [[ -f $PROVISIONED ]]; then
  echo "Skipping provisioning"
  exit
else
  echo "Provisioning"
  export DEBIAN_FRONTEND=noninteractive

  sudo add-apt-repository -y ppa:builds/sphinxsearch-rel22
  sudo apt-get update
  sudo apt-get install -y sphinxsearch
  sudo apt-get install -y hunspell
  sudo apt-get install -y hunspell-hu
  sudo apt-get install -y graphicsmagick
  sudo apt-get install -y texlive
  sudo apt-get install -y texlive-xetex
  sudo apt-get install -y texlive-latex-extra
  sudo apt-get install -y texlive-lang-hungarian
  sudo apt-get install -y fonts-linuxlibertine
  sudo mysql -u homestead -psecret < /home/vagrant/szentiras-hu/tmp/database.sql
  echo '[mysqld]' | sudo tee /etc/mysql/conf.d/szentiras-hu.cnf
  echo 'query_cache_type=1' | sudo tee --append /etc/mysql/conf.d/szentiras-hu.cnf
  sudo service mysql restart
  export APP_HOME=/home/vagrant/szentiras-hu
  sudo -H -u vagrant bash -c "cd $APP_HOME; export SZENTIRAS_WEBAPP_ENVIRONMENT=local; ./install.sh"

  sudo service sphinxsearch stop
  sudo cp $APP_HOME/deploy/local/sphinx/sphinx.conf /etc/sphinxsearch
  sudo indexer --all
  sudo service sphinxsearch start
  touch $PROVISIONED;
fi
 