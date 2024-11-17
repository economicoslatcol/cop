# Usa una imagen base de PHP con Apache preinstalado
FROM php:8.1-apache

# Instala extensiones necesarias (opcional, según tu proyecto)
RUN docker-php-ext-install mysqli

# Copia los archivos del proyecto al contenedor
COPY . /var/www/html/

# Exponer el puerto 80 para el servidor
EXPOSE 80
