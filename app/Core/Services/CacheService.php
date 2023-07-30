<?php

namespace App\Core\Services;

use App\Core\Controllers\RedisController;
use App\Core\Logger\LoggerInterface;
use App\Core\ORM\Entities\Post;
use App\Core\ORM\Entities\Schedule;

class CacheService
{
    private RedisController $redisController;


    private LoggerInterface $logger;

    public function __construct(
        RedisController $connection,
        LoggerInterface $logger
    )
    {
        $this->redisController = $connection;
        $this->logger = $logger;
    }

    public function setUserPage(int $user_id, string|int $page)
    {
        try {
            $this->redisController->setKey("PAGE_" . $user_id, $page, "60");
        } catch (\Exception $e) {
            $this->logger->warning("SetUserPageCache failed!", $e->getMessage());
        }
    }

    public function getUserPage(int $user_id): string|bool
    {
        try {
            return $this->redisController->getKey("PAGE_" . $user_id);
        } catch (\Exception $e) {
            $this->logger->warning("GetUserPageCache failed!", $e->getMessage());
            return false;
        }
    }

    public function createTempPost(int $user_id): int
    {
        $id = time();
        $post = new Post();
        $post->setId(null);
        $post->setCaption(null);
        $post->setMediaId(null);
        $post->setMediaType(null);
        $post->setKeyboard([]);
        $post->setNotifyEnabled(true);
        $post->setPreviewLinkAllowed(true);
        $post->setIsProtected(false);
        $post->setDatetimeScheduleSend(null);
        $post->setDatetimeScheduleEnd(null);
        $post->setTextFormat("Markdown");
        $post->setLinkMediaToCaption(false);
        $post->setCreatedBy($user_id);
        $this->redisController->setKey("NEW_POST_" . $id, serialize($post), 1800);
        return $id;
    }

    public function updateTempPost(int $post_id, Post $post): int
    {
        $this->redisController->setKey("NEW_POST_" . $post_id, serialize($post), 1800);
        return $post_id;
    }

    public function getTempPost(int $post_id): ?Post
    {
        if (($data = $this->redisController->getKey("NEW_POST_" . $post_id)) !== false) {
            return unserialize($data);
        }
        return null;
    }

    public function setChannelsToSend(int $postId, array $channels)
    {
        $this->redisController->setKey("CHANNELS_TO_SEND_" . $postId, json_encode($channels), 1800);
    }

    function getChannelsToSend(int $postId): ?array
    {
        if (($data = $this->redisController->getKey("CHANNELS_TO_SEND_" . $postId)) !== false) {
            return json_decode($data, true);
        }
        return [];
    }

}
