<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Comando para consumir filas RabbitMQ
 * Usado pelo supervisor para manter workers rodando continuamente
 */
class ConsumeRabbitMQCommand extends Command
{
    protected $signature = 'rabbitmq:consume {queue : Nome da fila a ser consumida}';
    
    protected $description = 'Consome mensagens de uma fila RabbitMQ específica';

    private AMQPStreamConnection $connection;
    private $channel;

    public function handle(): int
    {
        $queueName = $this->argument('queue');
        
        $this->info("Iniciando consumer para fila: {$queueName}");

        try {
            // Conectar ao RabbitMQ
            $this->connection = new AMQPStreamConnection(
                config('queue.connections.rabbitmq.host'),
                config('queue.connections.rabbitmq.port'),
                config('queue.connections.rabbitmq.login'),
                config('queue.connections.rabbitmq.password'),
                config('queue.connections.rabbitmq.vhost')
            );

            $this->channel = $this->connection->channel();

            // Declarar fila (idempotente)
            $this->channel->queue_declare(
                $queueName,
                false,  // passive
                true,   // durable
                false,  // exclusive
                false   // auto_delete
            );

            // Configurar prefetch para processar 1 mensagem por vez
            $this->channel->basic_qos(null, 1, null);

            // Callback para processar mensagens
            $callback = function (AMQPMessage $msg) use ($queueName) {
                $this->processMessage($msg, $queueName);
            };

            // Iniciar consumo
            $this->channel->basic_consume(
                $queueName,
                '',     // consumer_tag
                false,  // no_local
                false,  // no_ack (manual ack)
                false,  // exclusive
                false,  // nowait
                $callback
            );

            $this->info("Aguardando mensagens na fila: {$queueName}. Para parar: Ctrl+C");

            // Loop de consumo
            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Erro ao consumir fila: " . $e->getMessage());
            return 1;
        } finally {
            $this->closeConnection();
        }
    }

    /**
     * Processar mensagem recebida da fila
     */
    private function processMessage(AMQPMessage $msg, string $queueName): void
    {
        try {
            $data = json_decode($msg->body, true);
            
            $this->info("Processando mensagem da fila {$queueName}");
            $this->line("Dados: " . json_encode($data, JSON_PRETTY_PRINT));

            // Rotear para o processador correto baseado na fila
            $success = match ($queueName) {
                'docs.convert' => $this->handleDocumentConversion($data),
                'docs.extract_text' => $this->handleTextExtraction($data),
                'docs.merge' => $this->handlePdfMerge($data),
                'docs.sign' => $this->handlePdfSign($data),
                default => $this->handleDefaultQueue($data),
            };

            if ($success) {
                // Confirmar processamento (ACK)
                $msg->ack();
                $this->info("✓ Mensagem processada com sucesso");
            } else {
                // Rejeitar e reenviar para fila (NACK)
                $msg->nack(true);
                $this->warn("✗ Falha ao processar mensagem - reenviada para fila");
            }
        } catch (\Exception $e) {
            $this->error("Erro ao processar mensagem: " . $e->getMessage());
            
            // Rejeitar mensagem mas não reenviar (dead letter queue)
            $msg->nack(false);
        }
    }

    /**
     * Processar conversão de documentos
     */
    private function handleDocumentConversion(array $data): bool
    {
        // TODO: Chamar UseCase ou Service para converter documento
        $this->info("TODO: Implementar conversão de documento");
        return true;
    }

    /**
     * Processar extração de texto
     */
    private function handleTextExtraction(array $data): bool
    {
        // TODO: Chamar UseCase ou Service para extrair texto
        $this->info("TODO: Implementar extração de texto");
        return true;
    }

    /**
     * Processar merge de PDFs
     */
    private function handlePdfMerge(array $data): bool
    {
        // TODO: Chamar UseCase ou Service para fazer merge
        $this->info("TODO: Implementar merge de PDFs");
        return true;
    }

    /**
     * Processar assinatura digital
     */
    private function handlePdfSign(array $data): bool
    {
        // TODO: Chamar UseCase ou Service para assinar documento
        $this->info("TODO: Implementar assinatura digital");
        return true;
    }

    /**
     * Processar fila genérica
     */
    private function handleDefaultQueue(array $data): bool
    {
        $this->info("Processando fila genérica");
        return true;
    }

    /**
     * Fechar conexão ao finalizar
     */
    private function closeConnection(): void
    {
        try {
            if (isset($this->channel)) {
                $this->channel->close();
            }
            if (isset($this->connection)) {
                $this->connection->close();
            }
        } catch (\Exception $e) {
            $this->error("Erro ao fechar conexão: " . $e->getMessage());
        }
    }

    /**
     * Tratar sinais para shutdown graceful
     */
    public function __destruct()
    {
        $this->closeConnection();
    }
}
