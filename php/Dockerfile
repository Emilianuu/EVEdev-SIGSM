FROM php:apache
WORKDIR /var/www/html
COPY . /var/www/html/
EXPOSE 80
RUN apt-get update && apt-get install -y \
    vim \
    nano \
    iputils-ping \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mysqli

