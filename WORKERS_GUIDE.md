# Guia de Uso - RabbitMQ Workers e Processamento de Documentos

## 📋 Visão Geral

Este documento explica como utilizar o sistema de filas RabbitMQ para processamento assíncrono de documentos.

## 🔄 Arquitetura de Filas

### Filas Disponíveis

| Fila | Workers | Finalidade |
|------|---------|------------|
| `docs.convert` | 4 | Conversão de imagens e documentos para PDF |
| `docs.extract_text` | 2 | Extração de texto de PDFs |
| `docs.merge` | 2 | Merge de múltiplos PDFs |
| `docs.sign` | 2 | Assinatura digital de PDFs |

## 🚀 Como Enviar Mensagens para as Filas

### 1. Via PHP (Laravel)

```php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Conectar ao RabbitMQ
$connection = new AMQPStreamConnection(
    config('queue.connections.rabbitmq.host'),
    config('queue.connections.rabbitmq.port'),
    config('queue.connections.rabbitmq.user'),
    config('queue.connections.rabbitmq.password'),
    config('queue.connections.rabbitmq.vhost')
);

$channel = $connection->channel();

// Declarar fila
$channel->queue_declare('docs.convert', false, true, false, false);

// Criar mensagem
$data = [
    'task' => 'convert_image_to_pdf',
    'input_path' => '/path/to/image.jpg',
    'output_path' => '/path/to/output.pdf',
    'user_id' => 123,
];

$message = new AMQPMessage(
    json_encode($data),
    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
);

// Publicar na fila
$channel->basic_publish($message, '', 'docs.convert');

$channel->close();
$connection->close();
```

### 2. Via Artisan Command (recomendado)

```bash
# Criar um Job
php artisan make:job ConvertDocumentJob

# Disparar job para fila RabbitMQ
dispatch(new ConvertDocumentJob($data))->onQueue('docs.convert');
```

### 3. Via API REST

```bash
# Endpoint de exemplo (você precisa criar)
curl -X POST http://localhost/api/documents/convert \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: multipart/form-data" \
  -F "file=@/path/to/document.docx" \
  -F "format=pdf"
```

## 👷 Gerenciamento de Workers

### Comandos Supervisor

```bash
# Ver status de todos os workers
docker compose exec queue-worker-manager supervisorctl status

# Iniciar todos os workers
docker compose exec queue-worker-manager supervisorctl start all

# Parar todos os workers
docker compose exec queue-worker-manager supervisorctl stop all

# Reiniciar todos os workers
docker compose exec queue-worker-manager supervisorctl restart all

# Gerenciar fila específica (exemplo: docs.convert)
docker compose exec queue-worker-manager supervisorctl start rabbitmq-consumer-docs-convert:*
docker compose exec queue-worker-manager supervisorctl stop rabbitmq-consumer-docs-convert:*
docker compose exec queue-worker-manager supervisorctl restart rabbitmq-consumer-docs-convert:*

# Ver logs de worker específico
docker compose exec queue-worker-manager supervisorctl tail -f rabbitmq-consumer-docs-convert
```

### Ajustar Número de Workers

Edite `docker/supervisor/workers.conf`:

```ini
[program:rabbitmq-consumer-docs-convert]
numprocs=8  # Aumentar de 4 para 8 workers
```

Depois reinicie o container:

```bash
docker compose restart queue-worker-manager
```

## 📊 Monitoramento

### RabbitMQ Management UI

Acesse: http://localhost:15672
- Usuário: `guest`
- Senha: `guest`

**Funcionalidades:**
- Ver filas e número de mensagens
- Monitorar taxa de processamento
- Ver mensagens pendentes
- Gerenciar exchanges e bindings

### Logs dos Workers

```bash
# Logs em tempo real
docker compose logs -f queue-worker-manager

# Logs específicos de cada fila
docker compose exec app tail -f storage/logs/worker-docs-convert.log
docker compose exec app tail -f storage/logs/worker-docs-extract.log
docker compose exec app tail -f storage/logs/worker-docs-merge.log
docker compose exec app tail -f storage/logs/worker-docs-sign.log
```

## 🔧 Exemplo Completo: Processar Documento

### 1. Criar UseCase

```php
<?php

namespace Modules\Docs\Application\UseCases;

use Modules\Docs\Infrastructure\Services\DocumentService;

class ConvertDocumentUseCase
{
    public function __construct(
        private readonly DocumentService $documentService
    ) {}

    public function execute(string $inputPath, string $outputPath, string $type): bool
    {
        return match($type) {
            'image' => $this->documentService->convertImageToPdf($inputPath, $outputPath),
            'doc' => $this->documentService->convertDocumentToPdf($inputPath, dirname($outputPath)),
            default => false,
        };
    }
}
```

### 2. Criar Job

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Docs\Application\UseCases\ConvertDocumentUseCase;

class ConvertDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $inputPath,
        private readonly string $outputPath,
        private readonly string $type
    ) {}

    public function handle(ConvertDocumentUseCase $useCase): void
    {
        $success = $useCase->execute(
            $this->inputPath,
            $this->outputPath,
            $this->type
        );

        if (!$success) {
            throw new \Exception('Falha ao converter documento');
        }
    }
}
```

### 3. Disparar Job

```php
<?php

use App\Jobs\ConvertDocumentJob;

// Disparar para fila
ConvertDocumentJob::dispatch(
    inputPath: '/path/to/input.jpg',
    outputPath: '/path/to/output.pdf',
    type: 'image'
)->onQueue('docs.convert');
```

## 🛡️ Tratamento de Erros

### Retry Automático

Configure em `config/queue.php`:

```php
'rabbitmq' => [
    // ... outras configs
    'retry_after' => 90,
    'max_tries' => 3,
],
```

### Dead Letter Queue (DLQ)

Mensagens que falham após N tentativas vão para DLQ:

```bash
# Ver mensagens em DLQ via RabbitMQ Management UI
# http://localhost:15672/#/queues/%2F/docs.convert.dlq
```

### Logs de Erro

```bash
# Ver erros dos workers
docker compose exec app tail -f storage/logs/laravel.log | grep ERROR
```

## ⚡ Performance

### Métricas Importantes

- **Throughput**: Mensagens processadas por segundo
- **Latência**: Tempo médio de processamento
- **Taxa de erro**: Percentual de falhas

### Otimizações

1. **Aumentar workers** para filas com muitas mensagens
2. **Usar prefetch** para controlar quantas mensagens cada worker processa
3. **Monitorar memória** dos workers (supervisor reinicia automaticamente)
4. **Usar batch processing** para operações similares

## 🔐 Segurança

### Validação de Mensagens

Sempre valide dados recebidos das filas:

```php
public function handle(): void
{
    // Validar dados
    if (!file_exists($this->inputPath)) {
        throw new \InvalidArgumentException('Arquivo não encontrado');
    }

    // Validar tamanho
    if (filesize($this->inputPath) > 100 * 1024 * 1024) {
        throw new \InvalidArgumentException('Arquivo muito grande');
    }

    // Processar...
}
```

### Autenticação

Use JWT token ao enviar mensagens via API:

```php
// Incluir user_id na mensagem
$data = [
    'user_id' => auth()->id(),
    'input_path' => $path,
    // ...
];
```

## 📚 Recursos Adicionais

- [RabbitMQ Tutorial](https://www.rabbitmq.com/getstarted.html)
- [Laravel Queues Documentation](https://laravel.com/docs/10.x/queues)
- [Supervisor Documentation](http://supervisord.org/)

## 🆘 Troubleshooting

### Workers não estão processando

```bash
# Verificar se workers estão rodando
docker compose exec queue-worker-manager supervisorctl status

# Verificar conexão com RabbitMQ
docker compose exec app php artisan tinker
>>> use PhpAmqpLib\Connection\AMQPStreamConnection;
>>> $conn = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
>>> $conn->isConnected();
```

### Mensagens ficam presas na fila

```bash
# Ver mensagens na fila (RabbitMQ UI)
# http://localhost:15672

# Purgar fila (CUIDADO!)
docker compose exec app php artisan queue:clear rabbitmq --queue=docs.convert
```

### Worker travou/crash

```bash
# Supervisor reinicia automaticamente, mas você pode forçar:
docker compose exec queue-worker-manager supervisorctl restart rabbitmq-consumer-docs-convert:*

# Ver logs do crash
docker compose exec app cat storage/logs/worker-docs-convert.log
```

---

**Atualizado em:** Novembro 2024
