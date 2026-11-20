#!/bin/bash
set -e

# 1. Garante a existência dos diretórios essenciais do Laravel
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/storage/app/documentos_pacientes \
         /var/www/storage/app/public \
         /var/www/bootstrap/cache \
         /var/www/database

# 2. Cria arquivo de banco SQLite se a conexão for sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    if [ ! -f /var/www/database/database.sqlite ]; then
        echo "Criando arquivo de banco SQLite em /var/www/database/database.sqlite..."
        touch /var/www/database/database.sqlite
    fi
fi

# 3. Ajusta permissões de escrita para o usuário www-data
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database
chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database
if [ -f /var/www/database/database.sqlite ]; then
    chmod 664 /var/www/database/database.sqlite
fi

# 4. Cria arquivo .env caso não exista
if [ ! -f /var/www/.env ]; then
    if [ -f /var/www/.env.example ]; then
        echo "Copiando .env.example para .env..."
        cp /var/www/.env.example /var/www/.env
    fi
fi

# 5. Garante chave da aplicação APP_KEY
if [ -z "$APP_KEY" ]; then
    if ! grep -q "APP_KEY=base64:" /var/www/.env 2>/dev/null; then
        echo "Gerando APP_KEY do Laravel..."
        php /var/www/artisan key:generate --force
    fi
fi

# 6. Aguarda banco de dados se for MariaDB/MySQL externo
if [ -n "$DB_HOST" ] && [ "${DB_CONNECTION}" != "sqlite" ]; then
    echo "Aguardando conexão com o banco de dados em $DB_HOST:${DB_PORT:-3306}..."
    for i in {1..30}; do
        if nc -z "$DB_HOST" "${DB_PORT:-3306}" 2>/dev/null || (echo > /dev/tcp/"$DB_HOST"/"${DB_PORT:-3306}") 2>/dev/null; then
            echo "Banco de dados disponível!"
            break
        fi
        sleep 2
    done
fi

# 7. Cria link simbólico para storage público
php /var/www/artisan storage:link --force || true

# 8. Executa migrações e seeders se habilitado
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Executando migrações do banco de dados da Clínica..."
    php /var/www/artisan migrate --force || echo "Aviso: Migrations automáticas não foram executadas."
    echo "Executando seeders de configuração da Clínica..."
    php /var/www/artisan db:seed --force || echo "Aviso: Seeders automáticos não foram executados."
fi

# 9. Otimizações para ambiente de produção
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "Aplicando otimizações de cache (config, rotas, views)..."
    php /var/www/artisan config:cache || true
    php /var/www/artisan route:cache || true
    php /var/www/artisan view:cache || true
fi

echo "=========================================================="
echo "   SISTEMA CLÍNICA FABRIS INICIADO (Nginx + PHP-FPM)   "
echo "=========================================================="
exec "$@"
