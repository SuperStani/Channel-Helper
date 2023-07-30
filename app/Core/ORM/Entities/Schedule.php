<?php


namespace App\Core\ORM\Entities;


class Schedule
{
    private ?int $id;
    private ?string $datetimeSend;
    private ?string $datetimeEnd;
    private ?int $channelId;
    private ?int $postId;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getDatetimeSend(): ?string
    {
        return $this->datetimeSend;
    }

    /**
     * @param string|null $datetimeSend
     */
    public function setDatetimeSend(?string $datetimeSend): void
    {
        $this->datetimeSend = $datetimeSend;
    }

    /**
     * @return string|null
     */
    public function getDatetimeEnd(): ?string
    {
        return $this->datetimeEnd;
    }

    /**
     * @param string|null $datetimeEnd
     */
    public function setDatetimeEnd(?string $datetimeEnd): void
    {
        $this->datetimeEnd = $datetimeEnd;
    }

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



}