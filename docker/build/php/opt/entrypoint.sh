#!/bin/sh
set -e

ip route | head -n 1 | awk '{print $3 " host"}' >> /etc/hosts;

exec "$@"
