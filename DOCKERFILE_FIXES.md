# 🔧 Correções Aplicadas - Dockerfile

## Problema Identificado

O pacote `unoconv` não está disponível no repositório APT do Debian Trixie (testing), causando erro durante o build do Docker.

## Solução Implementada

### 1. Removido pacote `unoconv` do APT

### 2. Adicionadas dependências alternativas:
- ✅ `python3` - Runtime Python
- ✅ `python3-pip` - Gerenciador de pacotes Python
- ✅ `python3-uno` - Ponte Python-UNO para LibreOffice
- ✅ `libreoffice-writer` - Componente Writer do LibreOffice
- ✅ `libreoffice-calc` - Componente Calc do LibreOffice

### 3. Instalação unoconv via pip3:
```dockerfile
RUN pip3 install --break-system-packages unoconv || true
```
**Nota:** O flag `--break-system-packages` é necessário no Debian Trixie. O `|| true` garante que o build não falhe se unoconv não estiver disponível.

### 4. Arquivo `docker/php/custom.ini` criado
Arquivo de configuração PHP estava referenciado no docker-compose mas não existia.

---

## ✅ Conversão de Documentos

### LibreOffice Headless (Método Principal)
```php
// Funciona sem unoconv
$command = 'libreoffice --headless --convert-to pdf --outdir "output" "input.docx"';
```

**Formatos suportados:**
- `.doc`, `.docx` (Word)
- `.odt` (OpenDocument Text)
- `.rtf` (Rich Text Format)
- `.xls`, `.xlsx` (Excel)
- `.ods` (OpenDocument Spreadsheet)
- `.ppt`, `.pptx` (PowerPoint)

### Unoconv (Método Alternativo - Opcional)
```php
// Se unoconv estiver instalado via pip
$command = 'unoconv -f pdf "input.docx"';
```

---

## 🚀 Comandos de Build

### Build normal
```bash
docker compose build
```

### Build com cache limpo
```bash
docker compose build --no-cache
```

### Build apenas do serviço app
```bash
docker compose build app
```

---

## 🧪 Testar Conversão de Documentos

### 1. Entrar no container
```bash
docker compose exec app bash
```

### 2. Testar LibreOffice
```bash
libreoffice --version
# Deve retornar: LibreOffice 7.x.x.x

# Testar conversão
libreoffice --headless --convert-to pdf --outdir /tmp /path/to/test.docx
```

### 3. Testar unoconv (se instalado)
```bash
unoconv --version
# ou
python3 -m unoconv --version
```

### 4. Testar via PHP
```php
$service = app(\Modules\Docs\Infrastructure\Services\DocumentService::class);
$result = $service->convertDocumentToPdf('/path/to/input.docx', '/path/to/output');
var_dump($result); // Deve retornar caminho do PDF ou null
```

---

## 📝 Dependências Instaladas no Dockerfile

### Sistema Base
- ✅ build-essential (compiladores C/C++)
- ✅ git, curl, wget (ferramentas de download)
- ✅ nano, vim (editores de texto)
- ✅ unzip, zip (compressão)
- ✅ supervisor (gerenciador de processos)
- ✅ cron (agendamento de tarefas)
- ✅ ca-certificates (certificados SSL)
- ✅ openssl (criptografia)

### Bibliotecas de Desenvolvimento
- ✅ libzip-dev
- ✅ libonig-dev
- ✅ libpq-dev (PostgreSQL)
- ✅ libssl-dev
- ✅ libcurl4-openssl-dev
- ✅ libxml2-dev
- ✅ libpng-dev
- ✅ libjpeg-dev
- ✅ libfreetype6-dev
- ✅ libwebp-dev
- ✅ libmagickwand-dev (ImageMagick)

### Ferramentas de Documentos
- ✅ ghostscript (manipulação PDF)
- ✅ qpdf (manipulação PDF)
- ✅ poppler-utils (pdftotext, pdfinfo)
- ✅ imagemagick (manipulação de imagens)
- ✅ libreoffice + libreoffice-writer + libreoffice-calc
- ✅ python3 + python3-pip + python3-uno

### Extensões PHP
- ✅ pdo, pdo_pgsql, pgsql
- ✅ zip
- ✅ mbstring
- ✅ exif
- ✅ pcntl
- ✅ bcmath
- ✅ gd
- ✅ opcache
- ✅ sockets

### Extensões PHP via PECL
- ✅ redis 6.0.2
- ✅ imagick 3.7.0
- ✅ swoole 5.1.2
- ✅ protobuf 3.25.2

---

## ⚠️ Notas Importantes

1. **LibreOffice Headless é suficiente** para a maioria dos casos de uso de conversão de documentos Office para PDF.

2. **Unoconv** é uma ferramenta auxiliar que facilita o uso do LibreOffice, mas não é essencial.

3. **Python3-uno** fornece a ponte entre Python e LibreOffice, permitindo automação avançada se necessário.

4. **Primeira conversão pode ser lenta** - LibreOffice precisa inicializar na primeira vez (5-10 segundos). Conversões subsequentes são mais rápidas.

5. **Memory Usage** - LibreOffice pode consumir 200-500MB de RAM durante conversão. O limite de memória PHP está configurado para 1024MB.

---

## 🐛 Troubleshooting

### Erro: "libreoffice: command not found"
```bash
# Verificar se LibreOffice está instalado
docker compose exec app which libreoffice
docker compose exec app libreoffice --version

# Se não estiver, rebuild o container
docker compose build --no-cache app
```

### Erro: "Failed to convert document"
```bash
# Verificar logs
docker compose exec app tail -f storage/logs/laravel.log

# Testar conversão manual
docker compose exec app libreoffice --headless --convert-to pdf /path/to/file.docx
```

### Erro: "Permission denied"
```bash
# Ajustar permissões do diretório temporário
docker compose exec app chmod 777 /tmp

# Verificar permissões dos arquivos
docker compose exec app ls -la /path/to/files
```

---

## 📚 Referências

- [LibreOffice CLI Documentation](https://help.libreoffice.org/latest/en-US/text/shared/guide/convertfilters.html)
- [Python-UNO Bridge](https://wiki.documentfoundation.org/Documentation/DevGuide/Professional_UNO)
- [Docker PHP Official Images](https://hub.docker.com/_/php)

---

**Atualizado:** 26/11/2024  
**Status:** ✅ Corrigido e testado
