# 📍 ROTAS DA APLICAÇÃO - RESUMO

## 📂 **Localização dos Arquivos de Rotas**

```bash
/var/www/laravel-modular-clean/routes/
├── api.php       # Rotas da API REST (prefixo automático: /api)
├── web.php       # Rotas web tradicionais  
└── console.php   # Comandos Artisan personalizados
```

---

## ✅ **ROTAS FUNCIONANDO AGORA**

### 1. Health Check (Pública)
```bash
GET /api/health
```

**Teste:**
```bash
curl http://localhost:8000/api/health
```

**Resposta:**
```json
{
  "status": "ok",
  "timestamp": "2025-11-26T15:08:32-03:00",
  "service": "Laravel Modular Clean"
}
```

### 2. Home Web
```bash
GET /
```

**Teste:**
```bash
curl http://localhost:8000/
```

**Resposta:**
```json
{
  "message": "Laravel Modular Clean API",
  "version": "1.0.0",
  "documentation": "/api/documentation",
  "health": "/api/health"
}
```

### 3. Rotas de Debug (APP_DEBUG=true)
```bash
GET /api/debug/redis      # Testar Redis
GET /api/debug/database   # Testar PostgreSQL
GET /api/debug/rabbitmq   # Testar RabbitMQ
```

---

## ⚠️ **ROTAS DEFINIDAS MAS AGUARDANDO IMPLEMENTAÇÃO DOS CONTROLLERS**

Todas as rotas abaixo estão definidas em `routes/api.php` mas seus controllers ainda precisam ser criados:

### 🔐 Autenticação (Módulo Auth)
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
POST   /api/auth/refresh
GET    /api/auth/me
```

**Controller:** `Modules\Auth\Controllers\AuthController`  
**Status:** ⚠️ Precisa ser criado

---

### 👥 Usuários (Módulo Users)
```
GET    /api/users          # Listar
GET    /api/users/{id}     # Buscar
POST   /api/users          # Criar
PUT    /api/users/{id}     # Atualizar
DELETE /api/users/{id}     # Deletar
```

**Controller:** `Modules\Users\Controllers\UserController`  
**Status:** ⚠️ Precisa ser criado

---

### 📄 Documentos (Módulo Docs)
```
# Conversões
POST   /api/docs/convert/image-to-pdf
POST   /api/docs/convert/doc-to-pdf
POST   /api/docs/convert/pdf-to-images

# Extração
POST   /api/docs/extract/text

# Manipulação
POST   /api/docs/merge
POST   /api/docs/split

# Assinatura
POST   /api/docs/hash-pages
POST   /api/docs/sign

# CRUD
GET    /api/docs
GET    /api/docs/{id}
DELETE /api/docs/{id}
```

**Controller:** `Modules\Docs\Controllers\DocumentController`  
**Status:** ⚠️ Precisa ser criado

---

### 🔧 Workers/Filas (Módulo Workers)
```
GET    /api/workers/status
GET    /api/workers/jobs
POST   /api/workers/jobs/{id}/retry
DELETE /api/workers/jobs/{id}
```

**Controller:** `Modules\Workers\Controllers\WorkerController`  
**Status:** ⚠️ Precisa ser criado

---

## 📝 **Como Listar Rotas**

```bash
# Listar todas (quando controllers estiverem criados)
docker compose exec app php artisan route:list

# Filtrar por caminho
docker compose exec app php artisan route:list --path=auth

# Filtrar por método
docker compose exec app php artisan route:list --method=POST

# Ver apenas rotas de API
docker compose exec app php artisan route:list --path=api
```

---

## 🔑 **Middlewares Configurados**

| Middleware | Aplicação | Rotas |
|------------|-----------|-------|
| `auth:api` | JWT Guard | users, docs, workers |
| `throttle:60,1` | Rate Limit (60/min) | docs |
| `admin` | Verifica role admin | workers |

---

## 📊 **Status Atual**

| Componente | Status | Notas |
|------------|--------|-------|
| Arquivo `routes/api.php` | ✅ Criado | Todas rotas definidas |
| Arquivo `routes/web.php` | ✅ Criado | Home page |
| Arquivo `routes/console.php` | ✅ Criado | Inspire command |
| Health Check | ✅ Funcionando | Testado |
| Debug Routes | ✅ Funcionando | Redis, DB |
| Auth Controllers | ⚠️ Pendente | Criar controllers |
| Users Controllers | ⚠️ Pendente | Criar controllers |
| Docs Controllers | ⚠️ Pendente | Criar controllers |
| Workers Controllers | ⚠️ Pendente | Criar controllers |

---

## 🎯 **Próximos Passos**

1. ✅ Rotas definidas
2. ⚠️ Criar controllers nos módulos
3. ⚠️ Criar services/use cases
4. ⚠️ Criar repositories
5. ⚠️ Implementar lógica de negócio
6. ⚠️ Testes unitários

---

## 🧪 **Testes Rápidos das Rotas Funcionando**

```bash
# 1. Health Check
curl http://localhost:8000/api/health

# 2. Home
curl http://localhost:8000/

# 3. Debug Redis
curl http://localhost:8000/api/debug/redis

# 4. Debug Database
curl http://localhost:8000/api/debug/database
```

---

**Última Atualização:** 26/11/2025 15:10  
**Rotas Funcionando:** 5 (health, home, 3x debug)  
**Rotas Definidas:** 35 (aguardando controllers)

