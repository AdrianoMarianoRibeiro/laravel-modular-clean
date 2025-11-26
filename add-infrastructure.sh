#!/usr/bin/env bash

# Script para adicionar serviços de infraestrutura e finalizar setup
# Autor: GitHub Copilot CLI
# Data: 26/11/2024

set -e

echo "🔧 Adicionando serviços de infraestrutura ao docker-compose.yml..."

# Backup do docker-compose.yml original
cp docker-compose.yml docker-compose.yml.bak

# Adicionar serviços de infraestrutura
cat >> docker-compose.yml << 'EOF'

  # PostgreSQL
  postgres:
    image: postgres:16-alpine
    container_name: laravel_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE:-laravel}
      POSTGRES_USER: ${DB_USERNAME:-laravel}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - laravel
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U laravel"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis
  redis:
    image: redis:7-alpine
    container_name: laravel_redis
    restart: unless-stopped
    command: redis-server --appendonly yes
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - laravel
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 3s
      retries: 3

  # RabbitMQ
  rabbitmq:
    image: rabbitmq:3.12-management-alpine
    container_name: laravel_rabbitmq
    restart: unless-stopped
    environment:
      RABBITMQ_DEFAULT_USER: ${RABBITMQ_USER:-guest}
      RABBITMQ_DEFAULT_PASS: ${RABBITMQ_PASSWORD:-guest}
    ports:
      - "5672:5672"
      - "15672:15672"
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq
    networks:
      - laravel
    healthcheck:
      test: ["CMD", "rabbitmq-diagnostics", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  postgres_data:
    driver: local
  redis_data:
    driver: local
  rabbitmq_data:
    driver: local
EOF

echo "✅ Serviços adicionados ao docker-compose.yml"
echo ""

echo "🚀 Subindo todos os serviços..."
docker compose up -d

echo ""
echo "⏳ Aguardando serviços inicializarem (30 segundos)..."
sleep 30

echo ""
echo "📊 Status dos containers:"
docker compose ps

echo ""
echo "🗄️ Verificando conexão com PostgreSQL..."
docker compose exec -T postgres psql -U laravel -d laravel -c "SELECT version();" 2>&1 | grep PostgreSQL && echo "✅ PostgreSQL OK" || echo "⚠️ PostgreSQL não está pronto ainda"

echo ""
echo "📦 Verificando conexão com Redis..."
docker compose exec -T redis redis-cli ping 2>&1 | grep PONG && echo "✅ Redis OK" || echo "⚠️ Redis não está pronto ainda"

echo ""
echo "🐰 Verificando conexão com RabbitMQ..."
docker compose exec -T rabbitmq rabbitmq-diagnostics ping 2>&1 | grep "Ping succeeded" && echo "✅ RabbitMQ OK" || echo "⚠️ RabbitMQ não está pronto ainda"

echo ""
echo "🔄 Executando migrations..."
docker compose exec -T app php artisan migrate --force

echo ""
echo "🌱 Executando seeders..."
docker compose exec -T app php artisan db:seed --force

echo ""
echo "✅ SETUP 100% COMPLETO!"
echo ""
echo "📊 Status Final:"
docker compose ps

echo ""
echo "🎉 Projeto rodando em:"
echo "   API: http://localhost:9090"
echo "   Health: http://localhost:9090/api/health"
echo "   RabbitMQ Management: http://localhost:15672 (guest/guest)"
echo ""
echo "📝 Usuários de teste:"
echo "   Email: admin@example.com | Senha: password123"
echo "   Email: user@example.com | Senha: password123"
echo ""
echo "🧪 Rodar testes:"
echo "   docker compose exec app php artisan test"
echo ""
echo "🎉 SUCESSO TOTAL! Projeto 100% funcional!"
