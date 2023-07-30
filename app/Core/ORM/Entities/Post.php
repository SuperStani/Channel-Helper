<?php

namespace App\Core\ORM\Entities;

class Post
{
    private ?int $id;
    private ?string $caption;
    private ?string $mediaId;
    private ?string $mediaType;
    private bool $isProtected;
    private bool $previewLinkAllowed;
    private bool $notifyEnabled;
    private bool $linkMediaToCaption;
    private ?array $keyboard;
    private ?int $createdBy;
    private ?string $textFormat;
    private ?string $datetimeCreated;
    private ?int $privateTempMessageId;
    private ?string $datetimeScheduleSend;
    private ?string $datetimeScheduleEnd;
    private ?Schedule $schedule;


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
    public function getCaption(): ?string
    {
        return $this->caption;
    }

    /**
     * @param string|null $caption
     */
    public function setCaption(?string $caption): void
    {
        $this->caption = $caption;
    }

    /**
     * @return string|null
     */
    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    /**
     * @param string|null $mediaId
     */
    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return string|null
     */
    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    /**
     * @param string|null $mediaType
     */
    public function setMediaType(?string $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    /**
     * @return bool
     */
    public function isProtected(): bool
    {
        return $this->isProtected;
    }

    /**
     * @param bool $isProtected
     */
    public function setIsProtected(bool $isProtected): void
    {
        $this->isProtected = $isProtected;
    }

    /**
     * @return bool
     */
    public function isPreviewLinkAllowed(): bool
    {
        return $this->previewLinkAllowed;
    }

    /**
     * @param bool $previewLinkAllowed
     */
    public function setPreviewLinkAllowed(bool $previewLinkAllowed): void
    {
        $this->previewLinkAllowed = $previewLinkAllowed;
    }

    /**
     * @return bool
     */
    public function isNotifyEnabled(): bool
    {
        return $this->notifyEnabled;
    }

    /**
     * @param bool $notifyEnabled
     */
    public function setNotifyEnabled(bool $notifyEnabled): void
    {
        $this->notifyEnabled = $notifyEnabled;
    }

    /**
     * @return bool
     */
    public function isLinkMediaToCaption(): bool
    {
        return $this->linkMediaToCaption;
    }

    /**
     * @param bool $linkMediaToCaption
     */
    public function setLinkMediaToCaption(bool $linkMediaToCaption): void
    {
        $this->linkMediaToCaption = $linkMediaToCaption;
    }

    /**
     * @return ?array
     */
    public function getKeyboard(): ?array
    {
        return $this->keyboard;
    }

    /**
     * @param ?array $keyboard
     */
    public function setKeyboard(?array $keyboard): void
    {
        $this->keyboard = $keyboard;
    }

    /**
     * @return int|null
     */
    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    /**
     * @param int|null $createdBy
     */
    public function setCreatedBy(?int $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return string|null
     */
    public function getTextFormat(): ?string
    {
        return $this->textFormat;
    }

    /**
     * @param string|null $textFormat
     */
    public function setTextFormat(?string $textFormat): void
    {
        $this->textFormat = $textFormat;
    }

    /**
     * @return string|null
     */
    public function getDatetimeCreated(): ?string
    {
        return $this->datetimeCreated;
    }

    /**
     * @param string|null $datetimeCreated
     */
    public function setDatetimeCreated(?string $datetimeCreated): void
    {
        $this->datetimeCreated = $datetimeCreated;
    }

    /**
     * @return int|null
     */
    public function getPrivateTempMessageId(): ?int
    {
        return $this->privateTempMessageId;
    }

    /**
     * @param int|null $privateTempMessageId
     */
    public function setPrivateTempMessageId(?int $privateTempMessageId): void
    {
        $this->privateTempMessageId = $privateTempMessageId;
    }

    /**
     * @return string|null
     */
    public function getDatetimeScheduleSend(): ?string
    {
        return $this->datetimeScheduleSend;
    }

    /**
     * @param string|null $datetimeScheduleSend
     */
    public function setDatetimeScheduleSend(?string $datetimeScheduleSend): void
    {
        $this->datetimeScheduleSend = $datetimeScheduleSend;
    }

    /**
     * @return string|null
     */
    public function getDatetimeScheduleEnd(): ?string
    {
        return $this->datetimeScheduleEnd;
    }

    /**
     * @param string|null $datetimeScheduleEnd
     */
    public function setDatetimeScheduleEnd(?string $datetimeScheduleEnd): void
    {
        $this->datetimeScheduleEnd = $datetimeScheduleEnd;
    }

    /**
     * @return Schedule|null
     */
    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    /**
     * @param Schedule|null $schedule
     */
    public function setSchedule(?Schedule $schedule): void
    {
        $this->schedule = $schedule;
    }


}