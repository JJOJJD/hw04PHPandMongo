#!/bin/bash
# Render asigna el puerto mediante la variable de entorno PORT.
# Este script reemplaza el puerto por defecto de Apache (80) con el valor de $PORT.

PORT="${PORT:-80}"

# Actualizar configuración de Apache con el puerto asignado por Render
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/"          /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
