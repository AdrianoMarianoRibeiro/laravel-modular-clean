#!/usr/bin/env php
<?php

/**
 * Consumer RabbitMQ Standalone
 * 
 * Este script consome mensagens do RabbitMQ de forma independente do Laravel
 * Executado via Supervisor para garantir consumo contínuo e automático
 * 
 * Uso: php RabbitMQConsumer.php <queue_name>
 * Exemplo: php RabbitMQConsumer.php docs.convert
 */

require __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Validar argumentos
if ($argc < 2) {
    echo "Uso: php RabbitMQConsumer.php <queue_name>\n";
    exit(1);
}

$queueName = $argv[1];

// Carregar configurações do ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

// Configurações RabbitMQ
$rabbitmqHost = $_ENV['RABBITMQ_HOST'] ?? 'localhost';
$rabbitmqPort = (int) ($_ENV['RABBITMQ_PORT'] ?? 5672);
$rabbitmqUser = $_ENV['RABBITMQ_USER'] ?? 'guest';
$rabbitmqPassword = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

echo sprintf(
    "[%s] 🚀 Iniciando consumer para fila: %s\n",
    date('Y-m-d H:i:s'),
    $queueName
);

try {
    // Conectar ao RabbitMQ
    $connection = new AMQPStreamConnection(
        $rabbitmqHost,
        $rabbitmqPort,
        $rabbitmqUser,
        $rabbitmqPassword
    );
    
    $channel = $connection->channel();
    
    // Declarar exchange
    $channel->exchange_declare('docs_exchange', 'topic', false, true, false);
    
    // Declarar fila
    $channel->queue_declare($queueName, false, true, false, false);
    
    // Bind da fila ao exchange baseado no routing key
    $routingKey = str_replace('.', '_', $queueName);
    $channel->queue_bind($queueName, 'docs_exchange', $routingKey);
    
    echo sprintf(
        "[%s] ✅ Conectado ao RabbitMQ - Aguardando mensagens...\n",
        date('Y-m-d H:i:s')
    );
    
    // Callback para processar mensagens
    $callback = function (AMQPMessage $msg) use ($queueName) {
        $timestamp = date('Y-m-d H:i:s');
        $data = json_decode($msg->body, true);
        
        echo sprintf(
            "[%s] 📨 Mensagem recebida na fila %s\n",
            $timestamp,
            $queueName
        );
        
        try {
            // Processar mensagem baseado na fila
            processMessage($queueName, $data);
            
            // Acknowledge da mensagem
            $msg->ack();
            
            echo sprintf(
                "[%s] ✅ Mensagem processada com sucesso\n",
                date('Y-m-d H:i:s')
            );
            
        } catch (\Exception $e) {
            echo sprintf(
                "[%s] ❌ Erro ao processar mensagem: %s\n",
                date('Y-m-d H:i:s'),
                $e->getMessage()
            );
            
            // Rejeitar mensagem e reenviar para fila
            $msg->nack(true);
        }
    };
    
    // Configurar QoS - processar uma mensagem por vez
    $channel->basic_qos(null, 1, null);
    
    // Consumir mensagens
    $channel->basic_consume(
        $queueName,
        '',
        false,
        false,
        false,
        false,
        $callback
    );
    
    // Loop infinito aguardando mensagens
    while ($channel->is_consuming()) {
        $channel->wait();
    }
    
} catch (\Exception $e) {
    echo sprintf(
        "[%s] ❌ Erro fatal: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    exit(1);
}

/**
 * Processa mensagem baseado no tipo de fila
 */
function processMessage(string $queueName, array $data): void
{
    // Bootstrap do Laravel para acessar serviços
    static $app = null;
    
    if ($app === null) {
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
    
    switch ($queueName) {
        case 'docs.convert':
            processDocumentConversion($data);
            break;
            
        case 'docs.extract_text':
            processTextExtraction($data);
            break;
            
        case 'docs.merge':
            processPdfMerge($data);
            break;
            
        case 'docs.sign':
            processPdfSign($data);
            break;
            
        default:
            throw new \RuntimeException("Fila desconhecida: {$queueName}");
    }
}

/**
 * Processa conversão de documentos
 */
function processDocumentConversion(array $data): void
{
    echo "  → Convertendo documento: " . ($data['file'] ?? 'N/A') . "\n";
    
    // TODO: Implementar lógica de conversão
    // Exemplo: chamar UseCase de conversão
    // $useCase = app(Modules\Docs\Application\UseCases\ConvertDocumentUseCase::class);
    // $useCase->execute(ConvertDocumentDTO::fromArray($data));
    
    // Simulação de processamento
    sleep(2);
}

/**
 * Processa extração de texto
 */
function processTextExtraction(array $data): void
{
    echo "  → Extraindo texto de: " . ($data['file'] ?? 'N/A') . "\n";
    
    // TODO: Implementar lógica de extração
    sleep(1);
}

/**
 * Processa merge de PDFs
 */
function processPdfMerge(array $data): void
{
    echo "  → Fazendo merge de PDFs: " . count($data['files'] ?? []) . " arquivos\n";
    
    // TODO: Implementar lógica de merge
    sleep(2);
}

/**
 * Processa assinatura digital
 */
function processPdfSign(array $data): void
{
    echo "  → Assinando PDF: " . ($data['file'] ?? 'N/A') . "\n";
    
    // TODO: Implementar lógica de assinatura A1
    sleep(3);
}
