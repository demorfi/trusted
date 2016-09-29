#!/bin/bash

APACHE=$(which apachectl)

echo ""
echo "====================================================================="
echo "WARNING:"
echo "Make sure you have a volume mounted on '/data'!"
echo "Otherwise your certificates and database will be lost once the container gets destroyed."
echo "Default credentials:    admin / password."
echo "====================================================================="
echo ""

# run setup to make sure all required paths exist and database is seeded
php artisan trusted:setup

# make sure apache has write permissions to /data
chown -R www-data:www-data /data

$APACHE -DFOREGROUND -e info
