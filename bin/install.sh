#!/bin/bash

karmafw_dir=`dirname $0`/..
cd ${karmafw_dir}/../../..

cp -au vendor/karma-solutions/karmafw/example/* .


mkdir -p var/cache/templates
mkdir -p var/cache/sql
mkdir -p var/cache/cache
mkdir -p var/log
sudo chown www-data var -R

