<?php
/**
 * Script de teste: Envia job para RabbitMQ e testa as filas
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Carregar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Configurações
$host = $_ENV['RABBITMQ_HOST'] ?? 'localhost';
$port = (int) ($_ENV['RABBITMQ_PORT'] ?? 5672);
$user = $_ENV['RABBITMQ_USER'] ?? 'guest';
$password = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

echo "🚀 Conectando ao RabbitMQ...\n";

try {
    $connection = new AMQPStreamConnection($host, $port, $user, $password);
    $channel = $connection->channel();
    
    // Declarar exchange
    $channel->exchange_declare('docs_exchange', 'topic', false, true, false);
    
    echo "✅ Conectado com sucesso!\n\n";
    
    // Testar diferentes filas
    $tests = [
        [
            'queue' => 'docs.convert',
            'routing_key' => 'docs_convert',
            'data' => [
                'file_path' => '/tmp/test.jpg',
                'output_format' => 'pdf',
                'job_id' => uniqid('job_'),
            ]
        ],
        [
            'queue' => 'docs.extract_text',
            'routing_key' => 'docs_extract_text',
            'data' => [
                'file_path' => '/tmp/test.pdf',
                'job_id' => uniqid('job_'),
            ]
        ],
        [
            'queue' => 'docs.merge',
            'routing_key' => 'docs_merge',
            'data' => [
                'files' => ['/tmp/file1.pdf', '/tmp/file2.pdf'],
                'output_path' => '/tmp/merged.pdf',
                'job_id' => uniqid('job_'),
            ]
        ],
        [
            'queue' => 'docs.sign',
            'routing_key' => 'docs_sign',
            'data' => [
                'file_path' => '/tmp/document.pdf',
                'certificate_path' => '/tmp/cert.pfx',
                'job_id' => uniqid('job_'),
            ]
        ],
    ];
    
    foreach ($tests as $test) {
        echo "📤 Enviando mensagem para fila: {$test['queue']}\n";
        
        // Declarar fila
        $channel->queue_declare($test['queue'], false, true, false, false);
        
        // Bind
        $channel->queue_bind($test['queue'], 'docs_exchange', $test['routing_key']);
        
        // Criar mensagem
        $messageBody = json_encode($test['data']);
        $message = new AMQPMessage($messageBody, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'timestamp' => time(),
        ]);
        
        // Publicar
        $channel->basic_publish($message, 'docs_exchange', $test['routing_key']);
        
        echo "   ✅ Mensagem enviada: " . json_encode($test['data']) . "\n\n";
        
        sleep(1);
    }
    
    $channel->close();
    $connection->close();
    
    echo "\n✅ Todos os jobs foram enfileirados com sucesso!\n";
    echo "👀 Verifique os logs dos workers para ver o processamento.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
