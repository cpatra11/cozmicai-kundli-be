#!/bin/sh
set -e

# Start PHP-FPM in background
php-fpm -D

# Execute Caddy in foreground
exec "$@"
