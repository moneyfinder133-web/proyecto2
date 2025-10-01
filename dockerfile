FROM php:8.2-apache

# Copia tu código PHP dentro de la carpeta pública del contenedor
COPY . /var/www/html/

# Expone el puerto 80 para Render
EXPOSE 80
