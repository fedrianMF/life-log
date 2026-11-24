#!/bin/sh
set -euo pipefail

php artisan optimize
php artisan migrate --force

exec /usr/bin/supervisord -c /etc/supervisord.conf
