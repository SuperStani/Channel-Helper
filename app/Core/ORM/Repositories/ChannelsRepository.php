<?php


namespace App\Core\ORM\Repositories;


use App\Core\ORM\DB;
use App\Core\ORM\Entities\Channel;

class ChannelsRepository
{
    private DB $db;
    private SchedulesRepository $schedulesRepository;
    private MessagesRepository $messagesRepository;
    private static string $table = "Channels";

    public function __construct(
        DB $db,
        SchedulesRepository $schedulesRepository,
        MessagesRepository $messagesRepository
    )
    {
        $this->db = $db;
        $this->schedulesRepository = $schedulesRepository;
        $this->messagesRepository = $messagesRepository;
    }

    public function getAll(): array
    {
        $data = [];
        $sql = "SELECT * FROM " . self::$table;
        $res = $this->db->query($sql);
        foreach ($res as $ch) {
            $channel = new Channel();
            $channel->setId($ch['id']);
            $channel->setTitle($ch['title']);
            $data[] = $channel;
        }
        return $data;
    }

    public function getById(int $channelId): Channel
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE id = ?";
        $res = $this->db->query($sql, $channelId)->fetch();
        $channel = new Channel();
        $channel->setId($res['id'] ?? null);
        $channel->setTitle($res['title'] ?? null);
        return $channel;
    }

    public function save(Channel $channel): bool
    {
        $sql = "INSERT INTO " . self::$table . " SET title = ?, id = ?, added_by = ?";
        return $this->db->query($sql, $channel->getTitle(), $channel->getId(), $channel->getAddedBy()) != null;
    }

    public function delete(int $id): bool
    {
        $this->messagesRepository->deleteAllByChannelId($id);
        $this->schedulesRepository->deleteAllByChannelId($id);
        $sql = "DELETE FROM " . self::$table . " WHERE id = ?";
        $this->schedulesRepository->deleteAllByChannelId($id);
        return $this->db->query($sql, $id) != null;
    }
}