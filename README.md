<<<<<<< HEAD
# laravel-modular-clean
api em laravel com swoole
=======
# Laravel 10 - Arquitetura Modular com Clean Architecture

API Laravel 10 (PHP 8.3) construída com arquitetura modular seguindo princípios de Clean Architecture. O projeto utiliza Docker, Swoole/Octane para alta performance, PostgreSQL, Redis, RabbitMQ e inclui funcionalidades avançadas de manipulação de documentos (PDFs, imagens, Word, ODT).

## 📋 Características

- ✅ **Laravel 10** com PHP 8.3
- ✅ **Arquitetura Modular** (Clean Architecture)
- ✅ **Docker** (Dockerfile + docker-compose)
- ✅ **Swoole/Octane** para alta performance e concorrência
- ✅ **PostgreSQL** como banco de dados principal
- ✅ **Redis** para cache, session e rate limiting
- ✅ **RabbitMQ** para filas assíncronas
- ✅ **JWT Authentication** compatível com Swoole
- ✅ **Manipulação de documentos**: conversão de imagens, PDFs, Word, extração de texto
- ✅ **Merge de PDFs** usando Ghostscript
- ✅ **Assinatura digital** (skeleton para certificado A1 ICP-Brasil)
- ✅ **Rate Limiting** com bloqueio de IP por 5 minutos
- ✅ **Testes unitários** e feature tests
- ✅ **Workers RabbitMQ** gerenciados via Supervisor
- ✅ **Cron** para tarefas agendadas
- ✅ **Suporte a uploads grandes** (até 512MB) sem erro 413

## 🏗️ Estrutura do Projeto

```
laravel-modular-clean/
├── docker/                      # Configurações Docker
│   ├── nginx/                   # Configurações Nginx
│   ├── php/                     # Configurações PHP
│   ├── supervisor/              # Configurações Supervisor
│   └── cron/                    # Configurações Cron
├── modules/                     # Módulos da aplicação
│   ├── Users/                   # Módulo de usuários
│   │   ├── Domain/
│   │   │   ├── Entities/
│   │   │   └── Repositories/
│   │   ├── Application/
│   │   │   ├── UseCases/
│   │   │   └── DTOs/
│   │   ├── Infrastructure/
│   │   │   ├── Persistence/
│   │   │   └── Services/
│   │   └── Presentation/
│   │       ├── Controllers/
│   │       └── Requests/
│   ├── Auth/                    # Módulo de autenticação
│   ├── Docs/                    # Módulo de documentos
│   └── Workers/                 # Módulo de workers
├── app/
│   ├── Console/Commands/        # Comandos artisan
│   └── Providers/               # Service Providers
├── database/
│   ├── migrations/              # Migrations
│   └── seeders/                 # Seeders
├── routes/
│   └── api.php                  # Rotas da API
├── tests/                       # Testes
│   ├── Unit/                    # Testes unitários
│   └── Feature/                 # Testes de integração
├── Dockerfile                   # Imagem Docker
├── docker-compose.yml           # Orquestração de containers
└── README.md
```

## 🚀 Início Rápido

### Pré-requisitos

- Docker 24.0+
- Docker Compose 2.0+
- Git

### 1. Clonar e configurar

```bash
# Clonar repositório
git clone <seu-repositorio>
cd laravel-modular-clean

# Copiar arquivo de ambiente
cp .env.example .env

# Editar .env e configurar credenciais (DB, Redis, RabbitMQ)
nano .env
```

### 2. Build e inicializar containers

```bash
# Build da imagem e subir containers
docker compose up -d --build

# Aguardar containers iniciarem (30-60 segundos)
docker compose ps
```

### 3. Instalar dependências

```bash
# Entrar no container da aplicação
docker compose exec app bash

# Instalar dependências PHP
composer install

# Gerar chave da aplicação
php artisan key:generate

# Gerar chave JWT
php artisan jwt:secret

# Sair do container
exit
```

### 4. Executar migrations e seeders

```bash
# Rodar migrations
docker compose exec app php artisan migrate --seed

# Verificar usuários criados:
# - admin@example.com / password123
# - test@example.com / password123
```

### 5. Iniciar workers RabbitMQ

```bash
# Iniciar supervisor nos workers
docker compose exec queue-worker-manager supervisorctl start all

# Verificar status dos workers
docker compose exec queue-worker-manager supervisorctl status
```

### 6. Acessar aplicação

```bash
# Health check
curl http://localhost/api/health

# Testar registro de usuário
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Novo Usuario",
    "email": "novo@example.com",
    "password": "senha123456",
    "password_confirmation": "senha123456"
  }'

# Testar login
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

## 📡 Endpoints da API

### Autenticação

```bash
POST /api/auth/register       # Registrar novo usuário
POST /api/auth/login          # Login (retorna JWT token)
POST /api/auth/logout         # Logout (requer autenticação)
POST /api/auth/refresh        # Refresh token (requer autenticação)
GET  /api/auth/me             # Dados do usuário autenticado
```

### Usuários

```bash
GET /api/users/{id}           # Buscar usuário por ID (requer autenticação)
```

### Health Check

```bash
GET /api/health               # Verificar status da aplicação
```

## 🔧 Configurações Importantes

### Swoole/Octane - Evitar Erro 413

O projeto está configurado para aceitar uploads de até **512MB**:

**PHP** (`docker/php/custom.ini`):
```ini
upload_max_filesize = 512M
post_max_size = 512M
memory_limit = 1024M
```

**Swoole** (`.env`):
```env
SWOOLE_PACKAGE_MAX_LENGTH=536870912
SWOOLE_UPLOAD_MAX_FILESIZE=536870912
SWOOLE_POST_MAX_SIZE=536870912
```

**Nginx** (`docker/nginx/conf.d/laravel.conf`):
```nginx
client_max_body_size 512M;
client_body_buffer_size 512M;
```

### Rate Limiting / Proteção DDoS

O sistema bloqueia IPs que fazem mais de **100 requisições em 60 segundos**:

- Bloqueio automático por **5 minutos**
- Configurável em `modules/Auth/Infrastructure/Middleware/IpThrottleMiddleware.php`
- Usa Redis para armazenar contadores

### RabbitMQ Workers

Workers gerenciados pelo Supervisor processam filas automaticamente:

```bash
# Listar workers ativos
docker compose exec queue-worker-manager supervisorctl status

# Parar todos os workers
docker compose exec queue-worker-manager supervisorctl stop all

# Iniciar todos os workers
docker compose exec queue-worker-manager supervisorctl start all

# Reiniciar worker específico
docker compose exec queue-worker-manager supervisorctl restart rabbitmq-consumer-docs-convert:*
```

**Filas disponíveis:**
- `docs.convert` - Conversão de documentos (4 workers)
- `docs.extract_text` - Extração de texto (2 workers)
- `docs.merge` - Merge de PDFs (2 workers)
- `docs.sign` - Assinatura digital (2 workers)

### Cron / Schedule

Tarefas agendadas rodam automaticamente:

```bash
# Ver logs do scheduler
docker compose exec app tail -f storage/logs/scheduler.log

# Executar schedule manualmente
docker compose exec app php artisan schedule:run
```

## 🧪 Testes

### Executar testes unitários

```bash
# Todos os testes
docker compose exec app php artisan test

# Testes específicos
docker compose exec app php artisan test --filter CreateUserUseCaseTest
docker compose exec app php artisan test --filter AuthenticateUserUseCaseTest
docker compose exec app php artisan test --filter IpThrottleTest

# Com coverage (requer xdebug)
docker compose exec app php artisan test --coverage
```

### Testes disponíveis

- ✅ `CreateUserUseCaseTest` - Criação de usuários
- ✅ `AuthenticateUserUseCaseTest` - Autenticação JWT
- ✅ `IpThrottleTest` - Rate limiting e bloqueio de IP

## 📝 Manipulação de Documentos

### Serviços Disponíveis

O `DocumentService` oferece os seguintes métodos:

```php
// Converter imagem para PDF
convertImageToPdf(string $inputPath, string $outputPath): bool

// Converter Word/ODT para PDF (LibreOffice headless)
convertDocumentToPdf(string $inputPath, string $outputDir): ?string

// Converter PDF para imagens
convertPdfToImages(string $pdfPath, string $outputDir, string $format = 'jpg', int $dpi = 300): array

// Extrair texto de PDF (pdftotext)
extractTextFromPdf(string $pdfPath): string

// Fazer merge de PDFs (Ghostscript)
mergePdfs(array $pdfPaths, string $outputPath): bool

// Adicionar hash SHA256 por página (TODO: implementar com FPDI/FPDF)
addPageHashesToPdf(string $pdfPath, string $outputPath): bool

// Assinar PDF com certificado A1 (TODO: implementar PAdES)
signPdfWithCertificate(string $pdfPath, string $outputPath, string $certPath, string $certPassword): bool

// Anexar certificado de assinaturas ao PDF
attachSignatureCertificate(string $pdfPath, array $signatureData, string $outputPath): bool
```

### Exemplo de Uso

```php
use Modules\Docs\Infrastructure\Services\DocumentService;

$docService = app(DocumentService::class);

// Converter imagem para PDF
$success = $docService->convertImageToPdf('/path/to/image.jpg', '/path/to/output.pdf');

// Extrair texto de PDF
$text = $docService->extractTextFromPdf('/path/to/document.pdf');

// Merge de PDFs
$pdfs = ['/path/to/file1.pdf', '/path/to/file2.pdf'];
$docService->mergePdfs($pdfs, '/path/to/merged.pdf');
```

## 🔐 Assinatura Digital com Certificado A1

### TODO: Implementação Completa

A assinatura digital com certificado A1 (ICP-Brasil) está implementada como **skeleton**. Para implementação completa:

#### 1. Instalar certificado no container

```bash
# Copiar certificado .pfx para o container
docker cp /caminho/local/certificado.pfx laravel_app:/var/www/html/storage/certificates/

# Ajustar permissões
docker compose exec app chmod 600 storage/certificates/certificado.pfx
```

#### 2. Configurar .env

```env
CERTIFICATE_A1_PATH=/var/www/html/storage/certificates/certificado.pfx
CERTIFICATE_A1_PASSWORD=sua_senha_do_certificado
```

#### 3. Bibliotecas recomendadas para implementação PAdES

- **tcpdf** com suporte a assinatura digital
- **setasign/fpdi** + **setasign/fpdf** para manipulação avançada
- Integração com Java iText ou PDFBox (via shell)
- Serviço externo de assinatura (API)

#### 4. Requisitos ICP-Brasil

- Padrão **PAdES** (PDF Advanced Electronic Signatures)
- Suporte a **LTV** (Long Term Validation)
- Timestamp de autoridade certificadora (TSA)
- Validação de cadeia de certificação

**Referência**: [DOC-ICP-15.03 - Políticas de Assinatura Digital na ICP-Brasil](https://www.gov.br/iti/pt-br/centrais-de-conteudo/doc-icp-15-03-pdf)

## 🗄️ Banco de Dados

### Acessar PostgreSQL

```bash
# Via psql no container
docker compose exec postgres psql -U laravel -d laravel

# Via Adminer (interface web)
# Acesse: http://localhost:8080
# Sistema: PostgreSQL
# Servidor: postgres
# Usuário: laravel
# Senha: secret
# Base de dados: laravel
```

### Migrations

```bash
# Criar migration
docker compose exec app php artisan make:migration create_documents_table

# Rodar migrations
docker compose exec app php artisan migrate

# Rollback
docker compose exec app php artisan migrate:rollback

# Reset database
docker compose exec app php artisan migrate:fresh --seed
```

## 📊 Monitoramento

### Logs

```bash
# Logs da aplicação Laravel
docker compose logs -f app

# Logs do Nginx
docker compose logs -f nginx

# Logs dos workers
docker compose logs -f queue-worker-manager

# Logs do Swoole
docker compose exec app tail -f storage/logs/swoole.log

# Logs de requisições lentas
docker compose exec app tail -f storage/logs/swoole-slow.log
```

### RabbitMQ Management

Acesse a interface web do RabbitMQ:

```
URL: http://localhost:15672
Usuário: guest
Senha: guest
```

### Redis

```bash
# Conectar ao Redis
docker compose exec redis redis-cli

# Verificar chaves
KEYS *

# Monitorar comandos em tempo real
MONITOR
```

## 🛠️ Comandos Úteis

```bash
# Parar todos os containers
docker compose down

# Parar e remover volumes (limpa banco de dados)
docker compose down -v

# Rebuild de um serviço específico
docker compose up -d --build app

# Ver recursos utilizados
docker stats

# Limpar cache da aplicação
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear

# Otimizar para produção
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app composer install --optimize-autoloader --no-dev
```

## 🔒 Segurança

### Configurações de Produção

1. **Desabilitar debug**: `APP_DEBUG=false`
2. **Usar HTTPS**: Configurar certificado SSL no Nginx
3. **Senhas fortes**: Alterar senhas padrão do PostgreSQL, Redis, RabbitMQ
4. **JWT Secret**: Gerar nova chave segura
5. **Rate Limiting**: Ajustar limites conforme necessidade
6. **Firewall**: Expor apenas portas necessárias (80, 443)
7. **Backup**: Implementar rotina de backup do PostgreSQL

### TODO: Configurações Sensíveis

- [ ] Configurar credenciais S3 para armazenamento de arquivos
- [ ] Instalar e configurar certificado A1 para assinatura digital
- [ ] Implementar rotação de logs
- [ ] Configurar monitoring (Prometheus, Grafana, Sentry)
- [ ] Implementar autenticação 2FA (opcional)

## 📚 Tecnologias Utilizadas

- **PHP 8.3** com extensões: swoole, redis, imagick, pdo_pgsql, protobuf
- **Laravel 10** - Framework PHP
- **Laravel Octane** - Servidor de aplicação de alta performance
- **Swoole** - Servidor HTTP assíncrono
- **PostgreSQL 16** - Banco de dados relacional
- **Redis 7** - Cache, session e rate limiting
- **RabbitMQ 3** - Message broker para filas
- **Nginx** - Proxy reverso
- **Supervisor** - Gerenciador de processos
- **Docker** & **Docker Compose** - Containerização
- **ImageMagick** - Manipulação de imagens
- **Ghostscript** - Manipulação de PDFs
- **LibreOffice** - Conversão de documentos Office
- **Poppler Utils** - Extração de texto de PDFs
- **JWT** - Autenticação stateless

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 👥 Autores

- Time de Desenvolvimento

## 📞 Suporte

Para suporte e dúvidas, abra uma issue no repositório.

---

**Desenvolvido com ❤️ usando Laravel 10 e Clean Architecture**
>>>>>>> 0cc02a7 (feat: config :sparkles: - iniciando projeto)
