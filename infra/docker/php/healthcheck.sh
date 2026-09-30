#!/bin/sh
# Liveness of PHP-FPM via its internal ping path. For roles that do not run
# FPM (queue, scheduler) compose overrides the healthcheck.
set -eu
SCRIPT_NAME=/fpm-ping SCRIPT_FILENAME=/fpm-ping REQUEST_METHOD=GET \
  cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong
