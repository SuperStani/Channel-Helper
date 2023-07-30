<?php


namespace App\Core\RabbitLogic\Consumers;


use App\Core\Controllers\RabbitMQController;
use App\Core\Logger\LoggerInterface;
use App\Core\RabbitLogic\Handlers\PostsHandler;

class PostsConsumer extends BaseConsumer
{
    public function __construct(
        PostsHandler $handler,
        RabbitMQController $rabbitMQController,
        LoggerInterface $logger
    )
    {
        parent::__construct($handler, $rabbitMQController, $logger, "PostsQueue");
    }

    public function speedController($start_process, $stop_process): void
    {
        $iterations_per_seconds = 1;

        $usleep = (1000000 - $iterations_per_seconds * (($stop_process - $start_process) * 1000000)) / $iterations_per_seconds;
        $usleep = ($usleep > 0 ? $usleep : 0);
        usleep($usleep);
    }

    public function isAlreadyRunning(): bool
    {
        exec("ps aux | grep '{$this->processName}' | grep -v '>/dev/null 2>&1' | grep -v 'grep' | grep -v '" . getmypid() . "'| wc -l", $output, $returnValue);
        return $output[0] > 0;
    }

    public function stop(): bool
    {
        //$this->closeConnection();
        exec("pkill -f {$this->processName}", $output, $returnValue);

        // If pkill returns 0, the process was terminated successfully
        // If pkill returns 1, the process was not found
        // If pkill returns 2, an error occurred
        return $returnValue === 0;
    }
}