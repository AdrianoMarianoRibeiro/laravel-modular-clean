#!/usr/bin/env bash

# Script para finalizar setup do Laravel
set -e

echo "🔑 Gerando JWT secret..."
docker compose run --rm --entrypoint="" app php artisan jwt:secret

echo "🗄️ Rodando migrations..."
docker compose run --rm --entrypoint="" app php artisan migrate --force

echo "🌱 Rodando seeders..."
docker compose run --rm --entrypoint="" app php artisan db:seed --force

echo "🚀 Subindo containers..."
docker compose up -d

echo "⏳ Aguardando containers iniciarem..."
sleep 10

echo "✅ Setup completo!"
echo ""
echo "📊 Status dos containers:"
docker compose ps

echo ""
echo "🎉 Projeto Laravel rodando em:"
echo "   http://localhost:9090"
echo "   http://localhost:9090/api/health"
echo ""
echo "📝 Usuários de teste:"
echo "   Email: admin@example.com"
echo "   Senha: password123"
