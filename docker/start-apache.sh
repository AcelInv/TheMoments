#!/bin/sh
set -eu

# Render memberikan PORT saat container berjalan. Apache bawaan menggunakan
# port 80, jadi sesuaikan konfigurasi tanpa menyimpan port hosting di source.
port="${PORT:-10000}"
sed -ri "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s#<VirtualHost \*:80>#<VirtualHost *:${port}>#" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
