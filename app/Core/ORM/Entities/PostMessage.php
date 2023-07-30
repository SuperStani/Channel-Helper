<?php


namespace App\Core\ORM\Entities;


class PostMessage
{
    private ?int $channelId;
    private ?int $postId;
    private ?int $scheduleId;
    private ?int $messageId;

    /**
     * @return int|null
     */
    public function getChannelId(): ?int
    {
        return $this->channelId;
    }

    /**
     * @param int|null $channelId
     */
    public function setChannelId(?int $channelId): void
    {
        $this->channelId = $channelId;
    }

    /**
     * @return int|null
     */
    public function getPostId(): ?int
    {
        return $this->postId;
    }

    /**
     * @param int|null $postId
     */
    public function setPostId(?int $postId): void
    {
        $this->postId = $postId;
    }

    /**
     * @return int|null
     */
    public function getScheduleId(): ?int
    {
        return $this->scheduleId;
    }

    /**
     * @param int|null $scheduleId
     */
    public function setScheduleId(?int $scheduleId): void
    {
        $this->scheduleId = $scheduleId;
    }

    /**
     * @return int|null
     */
    public function getMessageId(): ?int
    {
        return $this->messageId;
    }

    /**
     * @param int|null $messageId
     */
    public function setMessageId(?int $messageId): void
    {
        $this->messageId = $messageId;
    }
}