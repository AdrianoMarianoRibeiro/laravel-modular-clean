#!/usr/bin/env bash

# Script de inicialização do projeto
# Automatiza a configuração inicial do ambiente

set -e

echo "🚀 Iniciando configuração do projeto..."

# Verificar se Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Inicie o Docker e tente novamente."
    exit 1
fi

# Build e iniciar containers
echo "📦 Building containers..."
docker compose up -d --build

# Aguardar serviços iniciarem
echo "⏳ Aguardando serviços iniciarem..."
sleep 30

# Instalar dependências
echo "📥 Instalando dependências..."
docker compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader

# Gerar chaves
echo "🔑 Gerando chaves da aplicação..."
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan jwt:secret --force

# Rodar migrations
echo "🗄️  Executando migrations..."
docker compose exec -T app php artisan migrate --force --seed

# Iniciar workers
echo "👷 Iniciando workers..."
docker compose exec -T queue-worker-manager supervisorctl start all

# Limpar caches
echo "🧹 Limpando caches..."
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan route:clear

echo ""
echo "✅ Projeto configurado com sucesso!"
echo ""
echo "📍 Endpoints disponíveis:"
echo "   - API: http://localhost/api"
echo "   - Health Check: http://localhost/api/health"
echo "   - RabbitMQ Management: http://localhost:15672 (guest/guest)"
echo "   - Adminer (DB): http://localhost:8080"
echo ""
echo "👤 Usuários de teste criados:"
echo "   - admin@example.com / password123"
echo "   - test@example.com / password123"
echo ""
echo "🔧 Comandos úteis:"
echo "   - Ver logs: docker compose logs -f app"
echo "   - Acessar container: docker compose exec app bash"
echo "   - Rodar testes: docker compose exec app php artisan test"
echo ""
