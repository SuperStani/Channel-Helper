<?php


namespace App\Core\Services;


use App\Core\Logger\LoggerInterface;
use App\Core\ORM\Repositories\PostsRepository;

class PostsInitializerService
{
    private PostsRepository $postsRepository;
    private PostQueueService $postQueueService;
    private LoggerInterface $logger;

    public function __construct(
        PostsRepository $postsRepository,
        PostQueueService $postQueueService,
        LoggerInterface $logger
    )
    {
        $this->postsRepository = $postsRepository;
        $this->postQueueService = $postQueueService;
        $this->logger = $logger;
    }

    public function initializePostsToSend()
    {
        $posts = $this->postsRepository->getScheduledPostsToSend();
        $this->logger->info("PostToSend", "Send " . count($posts) . " were sent to queue!");
        foreach ($posts as $post) {
            $this->postQueueService->reqSendPost($post->getId(), $post->getSchedule()->getChannelId(), $post->getSchedule()->getId());
        }
    }

    public function initializePostsToDelete()
    {
        $posts = $this->postsRepository->getScheduledPostsToDelete();
        $this->logger->info("PostToDelete", "Send " . count($posts) . " were sent to queue!");
        foreach ($posts as $post) {
            $this->postQueueService->reqDeletePost($post->getId(), $post->getSchedule()->getId());
        }
    }
}