<?php


namespace App\Core\Controllers;


use App\Configs\RabbitConfigurations;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQController
{
    private ?AMQPStreamConnection $connection;
    private ?AMQPChannel $channel;
    private ?string $queueName;

    public function __construct()
    {
        $this->connection = null;
    }

    public function init($queueName, $createExchange = true): void
    {
        if ($this->connection == null) {
            $this->queueName = $queueName;
            $this->connection = new AMQPStreamConnection(
                RabbitConfigurations::HOST,
                RabbitConfigurations::PORT,
                RabbitConfigurations::USER,
                RabbitConfigurations::PASSWORD
            );
            $this->channel = $this->connection->channel();
            $this->channel->queue_declare($queueName, false, true, false, false, false);

            $this->channel->exchange_declare($queueName . "MainExchange", 'direct', false, true, false);
            $this->channel->queue_bind($queueName, $queueName . "MainExchange");
        }
    }

    public function sendMessage(string $message, string $exchangeName): void
    {
        $message = new AMQPMessage($message, ['delivery_mode' => 2]);
        $this->channel->basic_publish(
            $message,
            $this->queueName . $exchangeName,
        );
    }

    public function getMessage(): ?string
    {
        $message = $this->channel->basic_get($this->queueName, false);
        if ($message) {
            $this->channel->basic_ack($message->delivery_info['delivery_tag']);
            return $message->body;
        }
        return null;
    }


    public function closeConnection(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}