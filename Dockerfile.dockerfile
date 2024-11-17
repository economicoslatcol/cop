# Usa una imagen base con PHP
FROM php:8.1-apache

# Copia los archivos de tu proyecto al contenedor
COPY . /var/www/html/

# Expon el puerto 80 para el servidor web
EXPOSE 80
