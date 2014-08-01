#!/bin/bash
STORAGE_DIR=/home/szentiras_hu_git/github/borazslo/production.szentiras.hu/app/storage
mkdir -p ${STORAGE_DIR}/sphinx
mkdir -p ${STORAGE_DIR}/logs/sphinx/
indexer --config /etc/sphinxsearch/sphinx_production.conf --all --rotate