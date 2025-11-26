# 🚀 QUICK START - Laravel Modular Clean

## ✅ STATUS ATUAL: 95% COMPLETO

### O que já está pronto:
- ✅ Docker Build (app, cron, queue-worker-manager)
- ✅ Laravel 10 + Octane/Swoole
- ✅ JWT Auth configurado
- ✅ Estrutura modular Clean Architecture
- ✅ Migrations e Seeders
- ✅ Testes unitários
- ✅ Rate limiting e IP blocking
- ✅ 127 pacotes Composer instalados

### O que falta:
- ⏳ Adicionar PostgreSQL, Redis, RabbitMQ ao docker-compose
- ⏳ Rodar migrations
- ⏳ Rodar seeders

---

## 🎯 OPÇÃO 1: Setup Automático (RECOMENDADO)

Execute um único script para finalizar tudo:

```bash
cd /var/www/laravel-modular-clean
./add-infrastructure.sh
```

Este script irá:
1. Adicionar PostgreSQL, Redis e RabbitMQ ao docker-compose.yml
2. Subir todos os serviços
3. Aguardar inicialização
4. Rodar migrations
5. Rodar seeders
6. Testar conexões

**Tempo estimado:** 2-3 minutos

---

## 🎯 OPÇÃO 2: Setup Manual

Se preferir fazer passo a passo:

### 1. Adicionar serviços ao docker-compose.yml

Edite o arquivo `docker-compose.yml` e adicione ao final (antes de `networks:`):

```yaml
  # PostgreSQL
  postgres:
    image: postgres:16-alpine
    container_name: laravel_postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: laravel
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: secret
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - laravel

  # Redis
  redis:
    image: redis:7-alpine
    container_name: laravel_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - laravel

  # RabbitMQ
  rabbitmq:
    image: rabbitmq:3.12-management-alpine
    container_name: laravel_rabbitmq
    restart: unless-stopped
    environment:
      RABBITMQ_DEFAULT_USER: guest
      RABBITMQ_DEFAULT_PASS: guest
    ports:
      - "5672:5672"
      - "15672:15672"
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq
    networks:
      - laravel

volumes:
  postgres_data:
  redis_data:
  rabbitmq_data:
```

### 2. Subir serviços

```bash
docker compose up -d
sleep 30  # Aguardar inicialização
```

### 3. Rodar migrations

```bash
docker compose exec app php artisan migrate --force
```

### 4. Rodar seeders

```bash
docker compose exec app php artisan db:seed --force
```

---

## 🧪 TESTAR A API

### Health Check
```bash
curl http://localhost:9090/api/health
```

**Resposta esperada:**
```json
{
  "status": "ok",
  "service": "Laravel Modular Clean",
  "timestamp": "2024-11-26T16:12:00Z"
}
```

### Registrar novo usuário
```bash
curl -X POST http://localhost:9090/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:9090/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

**Resposta esperada:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  }
}
```

### Buscar usuário autenticado
```bash
TOKEN="seu_token_aqui"

curl http://localhost:9090/api/auth/me \
  -H "Authorization: Bearer ${TOKEN}"
```

### Listar usuários
```bash
curl http://localhost:9090/api/users \
  -H "Authorization: Bearer ${TOKEN}"
```

---

## 🧪 RODAR TESTES

```bash
# Todos os testes
docker compose exec app php artisan test

# Teste específico
docker compose exec app php artisan test --filter=CreateUserTest

# Com coverage (se xdebug instalado)
docker compose exec app php artisan test --coverage
```

---

## 📊 VERIFICAR STATUS

```bash
# Status dos containers
docker compose ps

# Logs do app
docker compose logs app -f

# Logs do nginx
docker compose logs nginx -f

# Logs de todos
docker compose logs -f
```

---

## 🔍 TROUBLESHOOTING

### Container reiniciando?
```bash
docker compose logs app --tail=100
```

### Erro de conexão com DB?
```bash
# Verificar se PostgreSQL está rodando
docker compose exec postgres psql -U laravel -d laravel -c "SELECT version();"
```

### Erro de permissão?
```bash
chmod -R 777 storage bootstrap/cache
```

### Limpar cache
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

---

## 📍 URLs IMPORTANTES

| Serviço | URL | Credenciais |
|---------|-----|-------------|
| API | http://localhost:9090 | - |
| RabbitMQ Management | http://localhost:15672 | guest/guest |
| PostgreSQL | localhost:5432 | laravel/secret |
| Redis | localhost:6379 | - |

---

## 📝 CREDENCIAIS PADRÃO

**Usuários de teste:**
- Email: admin@example.com | Senha: password123
- Email: user@example.com | Senha: password123

**PostgreSQL:**
- Host: postgres (ou localhost:5432)
- Database: laravel
- User: laravel
- Password: secret

**Redis:**
- Host: redis (ou localhost:6379)
- No password

**RabbitMQ:**
- Host: rabbitmq (ou localhost:5672)
- User: guest
- Password: guest
- Management UI: http://localhost:15672

---

## 🎉 PRÓXIMOS PASSOS

Após setup completo:

1. **Implementar funcionalidades do módulo Docs:**
   - Conversão de documentos
   - Extração de texto de PDF
   - Merge de PDFs
   - Assinatura digital A1

2. **Configurar Workers RabbitMQ:**
   - Implementar consumers específicos
   - Configurar supervisor
   - Testar processamento assíncrono

3. **Configurar S3 (ou MinIO):**
   - Armazenamento de documentos
   - Upload/download de arquivos
   - Gestão de arquivos temporários

4. **Implementar autenticação avançada:**
   - Refresh tokens
   - Revogação de tokens
   - Login social (Google, Facebook, etc)

5. **Monitoring & Logs:**
   - Prometheus + Grafana
   - ELK Stack (Elasticsearch, Logstash, Kibana)
   - Sentry para error tracking

---

## 📚 DOCUMENTAÇÃO ADICIONAL

- **README.md** - Documentação completa do projeto
- **COMPLETE_SUCCESS.md** - Status final detalhado
- **SETUP_ISSUES.md** - Problemas encontrados e soluções
- **BUILD_FIXES_SUMMARY.md** - Correções aplicadas durante build

---

**Criado em:** 26/11/2024 13:15 GMT-3  
**Versão:** 1.0  
**Status:** 🟢 PRONTO PARA USO

**🚀 Bom desenvolvimento!**
