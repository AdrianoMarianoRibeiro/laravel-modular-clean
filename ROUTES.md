# 📍 ROTAS DA APLICAÇÃO

## 📂 Localização dos Arquivos

```
routes/
├── api.php      → Rotas da API (prefixo /api)
├── web.php      → Rotas web tradicionais
└── console.php  → Comandos Artisan via Closure
```

---

## 🌐 ROTAS API (routes/api.php)

Todas as rotas abaixo têm prefixo `/api`

### 1. ✅ Health Check (Pública)

```http
GET /api/health
```

**Resposta:**
```json
{
  "status": "ok",
  "timestamp": "2025-11-26T15:04:00-03:00",
  "service": "Laravel Modular Clean"
}
```

**Uso:** Monitoramento, load balancers, health checks

---

### 2. 🔐 MÓDULO AUTH - Autenticação JWT

#### Registrar Usuário (Pública)
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

#### Login (Pública)
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Resposta:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

#### Logout (Requer Auth)
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Refresh Token (Requer Auth)
```http
POST /api/auth/refresh
Authorization: Bearer {token}
```

#### Dados do Usuário Autenticado (Requer Auth)
```http
GET /api/auth/me
Authorization: Bearer {token}
```

---

### 3. 👥 MÓDULO USERS - Gestão de Usuários

**Todas requerem autenticação JWT**

#### Listar Usuários
```http
GET /api/users
Authorization: Bearer {token}
```

#### Buscar Usuário
```http
GET /api/users/{id}
Authorization: Bearer {token}
```

#### Criar Usuário
```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Maria Santos",
  "email": "maria@example.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

#### Atualizar Usuário
```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Maria Santos Silva"
}
```

#### Deletar Usuário (Soft Delete)
```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

---

### 4. 📄 MÓDULO DOCS - Manipulação de Documentos

**Todas requerem autenticação JWT + rate limit (60 req/min)**

#### Converter Imagem → PDF
```http
POST /api/docs/convert/image-to-pdf
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [arquivo.jpg|png|tiff]
```

#### Converter DOC/DOCX/ODT → PDF
```http
POST /api/docs/convert/doc-to-pdf
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [arquivo.doc|docx|odt]
```

#### Converter PDF → Imagens
```http
POST /api/docs/convert/pdf-to-images
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [arquivo.pdf]
dpi: 300 (opcional)
format: jpg|png (opcional)
```

#### Extrair Texto de PDF
```http
POST /api/docs/extract/text
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [arquivo.pdf]
```

#### Merge de PDFs
```http
POST /api/docs/merge
Authorization: Bearer {token}
Content-Type: multipart/form-data

files[]: [arquivo1.pdf]
files[]: [arquivo2.pdf]
files[]: [arquivo3.pdf]
```

#### Split PDF
```http
POST /api/docs/split
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [arquivo.pdf]
pages: "1-3,5,7-10" (opcional, padrão: todas)
```

#### Gerar Hash SHA256 por Página
```http
POST /api/docs/hash-pages
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [arquivo.pdf]
insert_in_document: true|false (opcional)
```

**Resposta:**
```json
{
  "hashes": [
    {"page": 1, "hash": "a3f2..."},
    {"page": 2, "hash": "b4e1..."}
  ],
  "document_url": "https://..." (se insert_in_document=true)
}
```

#### Assinar Documento (Certificado A1)
```http
POST /api/docs/sign
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [arquivo.pdf]
certificate: [certificado.pfx]
certificate_password: "senha_certificado"
reason: "Motivo da assinatura" (opcional)
location: "São Paulo" (opcional)
```

#### Listar Documentos Processados
```http
GET /api/docs
Authorization: Bearer {token}
```

#### Buscar Documento
```http
GET /api/docs/{id}
Authorization: Bearer {token}
```

#### Deletar Documento
```http
DELETE /api/docs/{id}
Authorization: Bearer {token}
```

---

### 5. 🔧 MÓDULO WORKERS - Administração de Filas

**Todas requerem autenticação JWT + role admin**

#### Status dos Workers
```http
GET /api/workers/status
Authorization: Bearer {token}
```

#### Listar Jobs na Fila
```http
GET /api/workers/jobs
Authorization: Bearer {token}
```

#### Reprocessar Job Falhado
```http
POST /api/workers/jobs/{id}/retry
Authorization: Bearer {token}
```

#### Deletar Job
```http
DELETE /api/workers/jobs/{id}
Authorization: Bearer {token}
```

---

### 6. 🐛 DEBUG (Apenas modo debug)

**Disponíveis apenas com APP_DEBUG=true**

#### Testar Redis
```http
GET /api/debug/redis
```

#### Testar Database
```http
GET /api/debug/database
```

#### Testar RabbitMQ
```http
GET /api/debug/rabbitmq
```

---

## 🌍 ROTAS WEB (routes/web.php)

```http
GET /
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

---

## 🔑 MIDDLEWARES APLICADOS

| Middleware | Descrição | Rotas Afetadas |
|------------|-----------|----------------|
| `auth:api` | JWT Authentication | Users, Docs, Workers |
| `throttle:60,1` | Rate Limit (60/min) | Docs |
| `admin` | Verifica role admin | Workers |

---

## 📊 COMANDOS ÚTEIS

### Listar todas as rotas
```bash
docker compose exec app php artisan route:list
```

### Listar rotas filtradas
```bash
docker compose exec app php artisan route:list --path=auth
docker compose exec app php artisan route:list --method=POST
```

### Limpar cache de rotas
```bash
docker compose exec app php artisan route:clear
```

### Cache de rotas (produção)
```bash
docker compose exec app php artisan route:cache
```

---

## 🧪 TESTES RÁPIDOS

### 1. Health Check
```bash
curl http://localhost:8000/api/health
```

### 2. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}'
```

### 3. Pegar Dados do Usuário
```bash
TOKEN="seu_token_aqui"

curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

### 4. Testar Rate Limit
```bash
# Enviar 70 requests rápidos para testar throttle
for i in {1..70}; do
  curl -s http://localhost:8000/api/health > /dev/null
  echo "Request $i"
done
```

---

## 📖 CONVENÇÕES

1. **Prefixo API:** Todas as rotas API têm `/api` automático
2. **Autenticação:** JWT via header `Authorization: Bearer {token}`
3. **Rate Limiting:** 60 requisições/minuto para rotas docs
4. **Soft Deletes:** DELETE não remove permanentemente (usa `deleted_at`)
5. **Validação:** Todas as requests usam FormRequest com validação forte
6. **Respostas:** JSON padronizado com status HTTP corretos

---

## 🔒 SEGURANÇA

- ✅ CORS configurado
- ✅ Rate limiting por IP (middleware throttle)
- ✅ JWT com expiração
- ✅ Passwords bcrypt
- ✅ Validação de inputs (FormRequest)
- ✅ CSRF protection (web routes)
- ✅ SQL injection protection (Eloquent ORM)

---

**Última Atualização:** 26/11/2025 15:05  
**Versão da API:** 1.0.0

