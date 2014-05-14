#!/bin/bash
STORAGE_DIR=/home/szentiras_hu_git/github/borazslo/staging.szentiras.hu/app/storage
mkdir -p ${STORAGE_DIR}/sphinx
mkdir -p ${STORAGE_DIR}/logs/sphinx/
indexer --config /etc/sphinxsearch/sphinx_staging.conf --all --rotate