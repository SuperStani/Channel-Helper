<?php


namespace App\Core\ORM\Repositories;


use App\Core\ORM\DB;
use App\Core\ORM\Entities\PostMessage;

class MessagesRepository
{
    private DB $db;
    private static string $table = "Messages";

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function save(PostMessage $message): bool
    {
        $sql = "INSERT INTO " . self::$table . " SET message_id = ?, channel_id = ?, post_id = ?, schedule_id = ?";
        return $this->db->query($sql, $message->getMessageId(), $message->getChannelId(), $message->getPostId(), $message->getScheduleId()) !== null;
    }

    public function getByScheduleId(int $schedule_id): PostMessage
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE schedule_id = ?";
        $res = $this->db->query($sql, $schedule_id)->fetch();
        $message = new PostMessage();
        $message->setScheduleId($schedule_id);
        $message->setMessageId($res['message_id'] ?? null);
        $message->setChannelId($res['channel_id'] ?? null);
        $message->setPostId($res['post_id'] ?? null);
        return $message;
    }

    public function deleteAllByChannelId(int $channel_id): bool
    {
        $sql = "DELETE FROM " . self::$table . " WHERE channel_id = ?";
        return $this->db->query($sql, $channel_id) != null;
    }
}