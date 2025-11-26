# ⚡ Comandos Úteis - Guia Rápido

## 🚀 Inicialização

```bash
# Inicialização automática
./setup.sh

# Ou manualmente
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

## 🐳 Docker

```bash
# Subir todos os serviços
docker compose up -d

# Ver status
docker compose ps

# Ver logs
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f queue-worker-manager

# Parar tudo
docker compose down

# Parar e remover volumes (limpa banco)
docker compose down -v

# Rebuild de serviço específico
docker compose up -d --build app

# Acessar container
docker compose exec app bash
docker compose exec postgres psql -U laravel -d laravel
docker compose exec redis redis-cli
```

## 📦 Composer

```bash
# Instalar dependências
docker compose exec app composer install

# Atualizar dependências
docker compose exec app composer update

# Adicionar pacote
docker compose exec app composer require vendor/package

# Otimizar autoloader (produção)
docker compose exec app composer dump-autoload -o
```

## 🎨 Artisan

```bash
# Listar comandos
docker compose exec app php artisan list

# Migrations
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:rollback
docker compose exec app php artisan migrate:fresh --seed

# Cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Otimizar (produção)
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Tinker (REPL)
docker compose exec app php artisan tinker

# Gerar chaves
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
```

## 🧪 Testes

```bash
# Todos os testes
docker compose exec app php artisan test

# Teste específico
docker compose exec app php artisan test --filter CreateUserUseCaseTest

# Com output detalhado
docker compose exec app php artisan test --verbose

# Paralelo (mais rápido)
docker compose exec app php artisan test --parallel

# Coverage (requer xdebug)
docker compose exec app php artisan test --coverage
```

## 👷 Workers / Filas

```bash
# Status dos workers
docker compose exec queue-worker-manager supervisorctl status

# Iniciar todos
docker compose exec queue-worker-manager supervisorctl start all

# Parar todos
docker compose exec queue-worker-manager supervisorctl stop all

# Reiniciar todos
docker compose exec queue-worker-manager supervisorctl restart all

# Worker específico
docker compose exec queue-worker-manager supervisorctl start rabbitmq-consumer-docs-convert:*
docker compose exec queue-worker-manager supervisorctl restart rabbitmq-consumer-docs-convert:*

# Ver logs de worker
docker compose exec queue-worker-manager supervisorctl tail -f rabbitmq-consumer-docs-convert
docker compose exec app tail -f storage/logs/worker-docs-convert.log

# Consumir fila manualmente
docker compose exec app php artisan rabbitmq:consume docs.convert
```

## 📊 RabbitMQ

```bash
# Management UI
open http://localhost:15672
# Login: guest / guest

# Listar filas
docker compose exec rabbitmq rabbitmqctl list_queues

# Purgar fila
docker compose exec rabbitmq rabbitmqctl purge_queue docs.convert

# Ver conexões
docker compose exec rabbitmq rabbitmqctl list_connections

# Ver consumers
docker compose exec rabbitmq rabbitmqctl list_consumers
```

## 💾 PostgreSQL

```bash
# Conectar via psql
docker compose exec postgres psql -U laravel -d laravel

# Backup
docker compose exec postgres pg_dump -U laravel laravel > backup.sql

# Restore
docker compose exec -T postgres psql -U laravel laravel < backup.sql

# Ver tabelas
docker compose exec postgres psql -U laravel -d laravel -c "\dt"

# Ver dados de tabela
docker compose exec postgres psql -U laravel -d laravel -c "SELECT * FROM users;"

# Adminer (UI web)
open http://localhost:8080
```

## 🔴 Redis

```bash
# Conectar
docker compose exec redis redis-cli

# Ver todas as chaves
KEYS *

# Ver chave específica
GET key_name

# Deletar chave
DEL key_name

# Limpar tudo (CUIDADO!)
FLUSHALL

# Monitorar comandos em tempo real
MONITOR

# Ver info
INFO

# TTL de chave
TTL key_name
```

## 📝 Logs

```bash
# Laravel logs
docker compose exec app tail -f storage/logs/laravel.log

# Nginx access
docker compose exec nginx tail -f /var/log/nginx/access.log

# Nginx error
docker compose exec nginx tail -f /var/log/nginx/error.log

# Swoole
docker compose exec app tail -f storage/logs/swoole.log

# Slow requests
docker compose exec app tail -f storage/logs/swoole-slow.log

# Scheduler
docker compose exec app tail -f storage/logs/scheduler.log

# Workers
docker compose exec app tail -f storage/logs/worker-docs-convert.log

# Filtrar erros
docker compose exec app tail -f storage/logs/laravel.log | grep ERROR
```

## 🔧 Permissões

```bash
# Ajustar permissões storage
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R laravel:laravel storage bootstrap/cache

# Se necessário (no host)
sudo chown -R $USER:$USER .
```

## 📈 Monitoring

```bash
# Ver recursos Docker
docker stats

# Ver processos
docker compose exec app ps aux

# Ver uso de memória
docker compose exec app free -h

# Ver espaço em disco
docker compose exec app df -h

# Top de processos
docker compose exec app htop
```

## 🔒 Segurança

```bash
# Gerar nova APP_KEY
docker compose exec app php artisan key:generate

# Gerar nova JWT_SECRET
docker compose exec app php artisan jwt:secret

# Alterar senha PostgreSQL
docker compose exec postgres psql -U laravel -c "ALTER USER laravel WITH PASSWORD 'nova_senha';"

# Ver IPs bloqueados (Redis)
docker compose exec redis redis-cli KEYS "blocked:ip:*"

# Remover bloqueio de IP
docker compose exec redis redis-cli DEL "blocked:ip:192.168.1.100"
```

## 🎯 Produção

```bash
# Otimizar tudo
docker compose exec app composer install --optimize-autoloader --no-dev
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Desabilitar debug
# Editar .env: APP_DEBUG=false

# Reiniciar Octane
docker compose restart app

# Ver status dos serviços
docker compose exec app php artisan octane:status
```

## 🐛 Debug / Troubleshooting

```bash
# Ver configurações
docker compose exec app php artisan config:show

# Ver rotas
docker compose exec app php artisan route:list

# Ver eventos
docker compose exec app php artisan event:list

# Verificar conexão DB
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Verificar Redis
docker compose exec app php artisan tinker
>>> Redis::ping();

# Verificar RabbitMQ
docker compose exec app php artisan tinker
>>> use PhpAmqpLib\Connection\AMQPStreamConnection;
>>> $c = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
>>> $c->isConnected();

# Limpar tudo e recriar
docker compose down -v
./setup.sh
```

## 📚 Criar Recursos

```bash
# Controller
docker compose exec app php artisan make:controller Api/DocumentController

# Model
docker compose exec app php artisan make:model Document -m

# Migration
docker compose exec app php artisan make:migration create_documents_table

# Seeder
docker compose exec app php artisan make:seeder DocumentsSeeder

# Request
docker compose exec app php artisan make:request StoreDocumentRequest

# Command
docker compose exec app php artisan make:command ProcessDocuments

# Job
docker compose exec app php artisan make:job ProcessDocumentJob

# Test
docker compose exec app php artisan make:test DocumentTest
docker compose exec app php artisan make:test DocumentTest --unit
```

## 🚦 Status / Health

```bash
# Health check da API
curl http://localhost/api/health

# Ver uptime
docker compose exec app uptime

# Ver versão PHP
docker compose exec app php -v

# Ver extensões PHP
docker compose exec app php -m

# Ver configurações PHP
docker compose exec app php -i

# Verificar ImageMagick
docker compose exec app convert -version

# Verificar LibreOffice
docker compose exec app libreoffice --version

# Verificar Ghostscript
docker compose exec app gs --version
```

## 🎨 Código / Formatação

```bash
# Formatar código (Pint)
docker compose exec app ./vendor/bin/pint

# Verificar código
docker compose exec app ./vendor/bin/pint --test

# PHPStan (análise estática - se instalado)
docker compose exec app ./vendor/bin/phpstan analyse
```

## 🔄 Git

```bash
# Status
git status

# Commit
git add .
git commit -m "feat: implementa feature X"

# Push
git push origin main

# Pull
git pull origin main

# Ver branches
git branch

# Criar branch
git checkout -b feature/nova-feature
```

## 📦 Backup / Restore

```bash
# Backup completo
docker compose exec postgres pg_dump -U laravel laravel > backup-$(date +%Y%m%d).sql
docker compose exec redis redis-cli SAVE

# Restore
docker compose exec -T postgres psql -U laravel laravel < backup-20241126.sql

# Backup volumes Docker
docker run --rm -v laravel-modular-clean_postgres_data:/data -v $(pwd):/backup ubuntu tar czf /backup/postgres-backup.tar.gz /data
```

---

**💡 Dica:** Adicione aliases no seu `.bashrc` ou `.zshrc`:

```bash
alias dce='docker compose exec app'
alias art='docker compose exec app php artisan'
alias composer='docker compose exec app composer'
alias test='docker compose exec app php artisan test'
```

Então você pode usar:
```bash
art migrate
composer install
test --filter UserTest
```
