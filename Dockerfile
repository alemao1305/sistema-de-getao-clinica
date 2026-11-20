# ==============================================================================
# ESTÁGIO 1: Build dos Ativos Frontend (Vite + Tailwind CSS + Alpine.js)
# ==============================================================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --prefer-offline
COPY . .
RUN npm run build

# ==============================================================================
# ESTÁGIO 2: Imagem de Produção (PHP 8.4-FPM + Nginx + Supervisor)
# ==============================================================================
FROM php:8.4-fpm

# Instala pacotes do sistema operacional, Nginx, Supervisor e utilitários
RUN apt-get update && apt-get install -y \
    git \
    curl \
    nginx \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    mariadb-client \
    netcat-traditional \
    && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP essenciais para o Laravel 11
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip opcache

# Copia binário oficial do Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Configurações do PHP, Nginx e Supervisor
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/nginx/nginx-site.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www

# Copia código-fonte da aplicação
COPY . /var/www

# Copia os assets compilados pelo Vite do primeiro estágio
COPY --from=frontend-builder /app/public/build /var/www/public/build

# Instala dependências PHP de produção (otimizado sem pacotes de desenvolvimento)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Garante permissões corretas para os diretórios graváveis do Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database

# Porta padrão de acesso HTTP
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
