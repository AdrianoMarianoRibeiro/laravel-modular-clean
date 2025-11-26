<?php

/**
 * Script para testar publicação de mensagens nas filas RabbitMQ
 */

require __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$rabbitmqHost = $_ENV['RABBITMQ_HOST'] ?? 'localhost';
$rabbitmqPort = (int) ($_ENV['RABBITMQ_PORT'] ?? 5672);
$rabbitmqUser = $_ENV['RABBITMQ_USER'] ?? 'guest';
$rabbitmqPassword = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

echo "🚀 Conectando ao RabbitMQ...\n";

$connection = new AMQPStreamConnection(
    $rabbitmqHost,
    $rabbitmqPort,
    $rabbitmqUser,
    $rabbitmqPassword
);

$channel = $connection->channel();
$channel->exchange_declare('docs_exchange', 'topic', false, true, false);

// Dados de teste
$testMessages = [
    [
        'queue' => 'docs.convert',
        'routing_key' => 'docs_convert',
        'data' => ['file' => 'document.docx', 'format' => 'pdf']
    ],
    [
        'queue' => 'docs.extract_text',
        'routing_key' => 'docs_extract_text',
        'data' => ['file' => 'document.pdf']
    ],
    [
        'queue' => 'docs.merge',
        'routing_key' => 'docs_merge',
        'data' => ['files' => ['doc1.pdf', 'doc2.pdf', 'doc3.pdf']]
    ],
    [
        'queue' => 'docs.sign',
        'routing_key' => 'docs_sign',
        'data' => ['file' => 'contract.pdf', 'certificate' => 'A1']
    ],
];

foreach ($testMessages as $test) {
    $message = new AMQPMessage(
        json_encode($test['data']),
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
    );
    
    $channel->basic_publish($message, 'docs_exchange', $test['routing_key']);
    
    echo "✅ Mensagem publicada na fila: {$test['queue']}\n";
    echo "   Dados: " . json_encode($test['data']) . "\n\n";
}

$channel->close();
$connection->close();

echo "✨ Todas as mensagens foram publicadas com sucesso!\n";
echo "📊 Verifique os logs dos workers com:\n";
echo "   docker compose logs -f queue-worker-manager\n";
