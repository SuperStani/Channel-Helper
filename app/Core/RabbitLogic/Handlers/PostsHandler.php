<?php


namespace App\Core\RabbitLogic\Handlers;


use App\Core\Services\PostsManagerService;

class PostsHandler extends BaseHandler
{
    private PostsManagerService $postSenderService;

    public function __construct(
        PostsManagerService $postSenderService
    )
    {
        $this->postSenderService = $postSenderService;
    }

    public function handle(string $message)
    {
        $info = json_decode($message, true);
        if($info['type'] == 'TO_SEND') {
            $this->postSenderService->sendPost($info['post_id'], $info['channel_id'], $info['schedule_id']);
            echo "TO_SEND" . PHP_EOL;
        } else {
            echo "TO_DELETE" . PHP_EOL;
            $this->postSenderService->deletePost($info['post_id'], $info['schedule_id']);
        }
    }
}