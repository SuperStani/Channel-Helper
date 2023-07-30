<?php


namespace App\Core\ORM\Repositories;


use App\Core\ORM\DB;
use App\Core\ORM\Entities\Post;

class PostsRepository
{
    private DB $db;
    private SchedulesRepository $schedulesRepository;
    private static string $table = "Posts";

    public function __construct(DB $db, SchedulesRepository $schedulesRepository)
    {
        $this->db = $db;
        $this->schedulesRepository = $schedulesRepository;
    }

    public function save(Post $post): bool
    {
        $sql = "INSERT INTO " . self::$table;
        $sql .= " SET caption = ?, media_id = ?, media_type = ?, is_protected = ?, text_format = ?, is_preview_link_allowed = ?";
        $sql .= ", is_link_media_to_caption = ?, created_by = ?, is_notify_enabled = ?, keyboard = ?";
        $res = $this->db->query(
            $sql,
            $post->getCaption(),
            $post->getMediaId(),
            $post->getMediaType(),
            (int)$post->isProtected(),
            $post->getTextFormat(),
            (int)$post->isPreviewLinkAllowed(),
            (int)$post->isLinkMediaToCaption(),
            $post->getCreatedBy(),
            (int)$post->isNotifyEnabled(),
            json_encode($post->getKeyboard())
        );
        $post->setId($this->db->getLastInsertId());
        return $res !== null;
    }

    public function update(Post $post): bool
    {
        $sql = "UPDATE " . self::$table;
        $sql .= " SET caption = ?, media_id = ?, media_type = ?, is_protected = ?, text_format = ?, is_preview_link_allowed = ?";
        $sql .= " is_link_media_to_caption = ?, created_by = ?, is_notify_enabled = ?, keyboard = ? WHERE id = ?";
        $res = $this->db->query(
            $sql,
            $post->getCaption(),
            $post->getMediaId(),
            $post->getMediaType(),
            (int)$post->isProtected(),
            $post->getTextFormat(),
            (int)$post->isPreviewLinkAllowed(),
            (int)$post->isLinkMediaToCaption(),
            $post->getCreatedBy(),
            (int)$post->isNotifyEnabled(),
            json_encode($post->getKeyboard()),
            $post->getId()
        );
        return $res !== null;
    }

    public function getById(int $id): Post
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE id = ?";
        $res = $this->db->query($sql, $id)->fetch();
        $post = new Post();
        $post->setId($res['id'] ?? null);
        $post->setCaption($res['caption'] ?? null);
        $post->setMediaId($res['media_id'] ?? null);
        $post->setMediaType($res['media_type'] ?? null);
        $post->setKeyboard(json_decode($res['keyboard'], true) ?? null);
        $post->setNotifyEnabled((bool)$res['is_notify_enabled'] ?? null);
        $post->setPreviewLinkAllowed((bool)$res['is_preview_link_allowed'] ?? null);
        $post->setIsProtected((bool)$res['is_protected'] ?? null);
        $post->setTextFormat($res['text_format'] ?? null);
        $post->setLinkMediaToCaption((bool)$res['is_link_media_to_caption']) ?? null;
        $post->setCreatedBy($res['created_by'] ?? null);
        return $post;
    }

    public function getScheduledPostsToSend(): array
    {
        $data = [];
        $schedules = $this->schedulesRepository->getSchedulesToSend();
        foreach ($schedules as $schedule) {
            $post = $this->getById($schedule->getPostId());
            $post->setSchedule($schedule);
            $data[] = $post;
        }
        return $data;
    }
    public function getScheduledPostsToDelete(): array
    {
        $data = [];
        $schedules = $this->schedulesRepository->getSchedulesToDelete();
        foreach ($schedules as $schedule) {
            $post = $this->getById($schedule->getPostId());
            $post->setSchedule($schedule);
            $data[] = $post;
        }
        return $data;
    }

    public function getPostsNotProcessedYet(?int $limit = null, ?int $offset = null): array
    {
        $data = [];
        $schedules = $this->schedulesRepository->getAllSchedulesNotProcessedYet($limit, $offset);
        foreach ($schedules as $schedule) {
            $post = $this->getById($schedule->getPostId());
            $post->setSchedule($schedule);
            $data[] = $post;
        }
        return $data;
    }

}