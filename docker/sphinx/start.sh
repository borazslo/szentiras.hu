#!/usr/bin/env bash
set -e
cp /etc/sphinxsearch/sphinx.conf.in /etc/sphinxsearch/sphinx.conf
mkdir -p /storage/sphinx

echo "Prepare sphinx.conf"

sed -i "s/__DB_TYPE__/${DB_TYPE}/g" /etc/sphinxsearch/sphinx.conf
sed -i "s/__DB_HOST__/${DB_HOST}/g" /etc/sphinxsearch/sphinx.conf
sed -i "s/__DB_USERNAME__/${DB_USERNAME}/g" /etc/sphinxsearch/sphinx.conf
sed -i "s/__DB_PASSWORD__/${DB_PASSWORD}/g" /etc/sphinxsearch/sphinx.conf
sed -i "s/__DB_DATABASE__/${DB_DATABASE}/g" /etc/sphinxsearch/sphinx.conf
sed -i "s/__DB_PORT__/${DB_PORT}/g" /etc/sphinxsearch/sphinx.conf
sed -i "s/__SPHINX_PORT__/${SPHINX_PORT}/g" /etc/sphinxsearch/sphinx.conf

echo "Prepare sphinx.conf done"
echo "Start indexer"
indexer --all
echo "Start indexer done"
echo "Start searchd"
searchd -c /etc/sphinxsearch/sphinx.conf --nodetach
