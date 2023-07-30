<?php


namespace App\Core\Services;


use App\Core\Controllers\RabbitMQController;
use App\Core\ORM\Repositories\SchedulesRepository;

class PostQueueService
{
    public RabbitMQController $rabbitMQController;
    public SchedulesRepository $schedulesRepository;

    public function __construct(
        RabbitMQController $rabbitMQController,
        SchedulesRepository $schedulesRepository
    )
    {
        $this->rabbitMQController = $rabbitMQController;
        $this->schedulesRepository = $schedulesRepository;
    }

    public function reqSendPost(int $postId, int $channelId, ?int $scheduleId = null)
    {
        $this->rabbitMQController->init("PostsQueue", false);
        $message = [
            "type" => "TO_SEND",
            "post_id" => $postId,
            "channel_id" => $channelId,
            "schedule_id" => $scheduleId
        ];
        $this->rabbitMQController->sendMessage(json_encode($message), "MainExchange");
        if($scheduleId !== null) {
            $this->schedulesRepository->updateStatus($scheduleId, "QUEUED_TO_SEND");
        }
    }

    public function reqDeletePost(int $postId, int $scheduleId)
    {
        $this->rabbitMQController->init("PostsQueue", false);
        $message = [
            "type" => "TO_DELETE",
            "post_id" => $postId,
            "schedule_id" => $scheduleId
        ];
        $this->rabbitMQController->sendMessage(json_encode($message), "MainExchange");
    }
}