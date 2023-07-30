<?php


namespace App\Core\RabbitLogic\Consumers;


use App\Core\Controllers\RabbitMQController;
use App\Core\Logger\LoggerInterface;
use App\Core\RabbitLogic\Handlers\BaseHandler;

abstract class BaseConsumer
{
    private BaseHandler $handler;
    private RabbitMQController $rabbitMQController;
    private LoggerInterface $logger;
    private string $queueName;
    protected string $processName;

    public function __construct(
        BaseHandler $handler,
        RabbitMQController $rabbitMQController,
        LoggerInterface $logger,
        string $queueName,
    )
    {
        $this->handler = $handler;
        $this->rabbitMQController = $rabbitMQController;
        $this->logger = $logger;
        $this->queueName = $queueName;
    }

    public function init(string $processName) {
        $this->processName = $processName;
    }

    public function start()
    {
        if ($this->isAlreadyRunning()) {
            die("Lo script è gia in esecuzione");
        }
        $this->rabbitMQController->init($this->queueName);
        $this->run();
    }

    public function run()
    {
        $time_start = microtime(true);
        while (true) {
            $this->speedController($time_start, microtime(true));
            $message = $this->rabbitMQController->getMessage();
            if ($message == null) {
                echo "No Message" . PHP_EOL;
                usleep(700000);
                continue;
            }
            echo $message . PHP_EOL;
            $this->handler->handle($message);
        }
    }

    protected function closeConnection(): void
    {
        $this->rabbitMQController->closeConnection();
    }

    abstract function speedController($start_process, $stop_process);

    abstract function isAlreadyRunning(): bool;

    abstract function stop();
}