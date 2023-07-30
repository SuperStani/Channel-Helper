<?php


namespace App\Core\ORM\Entities;


class Channel
{
    private int $id;
    private string $title;
    private int $added_by;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getAddedBy(): int
    {
        return $this->added_by;
    }

    /**
     * @param int $added_by
     */
    public function setAddedBy(int $added_by): void
    {
        $this->added_by = $added_by;
    }
}