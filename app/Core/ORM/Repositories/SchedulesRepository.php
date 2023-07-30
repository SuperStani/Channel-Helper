<?php


namespace App\Core\ORM\Repositories;


use App\Core\ORM\DB;
use App\Core\ORM\Entities\Schedule;

class SchedulesRepository
{
    private DB $db;
    private static string $table = "Schedules";

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function deleteAllByChannelId(int $channel_id): bool
    {
        $sql = "DELETE FROM " . self::$table . " WHERE channel_id = ?";
        return $this->db->query($sql, $channel_id) != null;
    }

    public function save(Schedule $schedule): ?string
    {
        $sql = "INSERT INTO " . self::$table . " SET datetimeSend = ?, datetimeEnd = ?, post_id = ?, channel_id = ?";
        $res = $this->db->query($sql, $schedule->getDatetimeSend(), $schedule->getDatetimeEnd(), $schedule->getPostId(), $schedule->getChannelId()) != null;
        return $this->db->getLastInsertId();
    }

    public function deleteById(int $id): bool
    {
        $sql = "DELETE FROM " . self::$table . " WHERE id = ?";
        return $this->db->query($sql, $id) != null;
    }

    public function getSchedulesToSend(): array
    {
        $data = [];
        $sql = "SELECT * FROM " . self::$table . " s WHERE s.status = 'NOT_PROCESSED' AND s.datetimeSend is NOT NULL AND s.datetimeSend <= NOW();";
        $res = $this->db->query($sql);
        if ($res->rowCount() > 0) {
            foreach ($res as $row) {
                $schedule = new Schedule();
                $schedule->setId($row['id']);
                $schedule->setChannelId($row['channel_id']);
                $schedule->setPostId($row['post_id']);
                $data[] = $schedule;
            }
        }

        return $data;
    }

    public function getSchedulesToDelete(): array
    {
        $data = [];
        $sql = "SELECT * FROM " . self::$table . " s WHERE s.status NOT IN('QUEUED_TO_DELETE', 'DELETED') AND s.datetimeEnd is NOT NULL AND s.datetimeEnd <= NOW()";
        $res = $this->db->query($sql);
        if ($res->rowCount() > 0) {
            foreach ($res as $row) {
                $schedule = new Schedule();
                $schedule->setId($row['id']);
                $schedule->setChannelId($row['channel_id']);
                $schedule->setPostId($row['post_id']);
                $data[] = $schedule;
            }
        }
        return $data;
    }

    public function updateStatus(int $scheduleId, string $status): bool
    {
        $sql = "UPDATE " . self::$table . " s SET s.status = ? WHERE s.id = ?";
        return $this->db->query($sql, $status, $scheduleId) != null;
    }

    public function getAllSchedulesNotProcessedYet(?int $limit = null, ?int $offset = null): array
    {
        $data = [];
        $sql = "SELECT * FROM " . self::$table . " WHERE datetimeSend is not NULL AND datetimeSend > NOW() OR datetimeEnd is not NULL AND datetimeEnd > NOW() ORDER by id";
        if($limit !== null) {
            if($offset !== null) {
                $sql .= " LIMIT $offset, $limit";
            } else {
                $sql .= " LIMIT " . $limit;
            }
        }
        $res = $this->db->query($sql);
        if ($res->rowCount() > 0) {
            foreach ($res as $row) {
                $schedule = new Schedule();
                $schedule->setId($row['id']);
                $schedule->setChannelId($row['channel_id']);
                $schedule->setPostId($row['post_id']);
                $schedule->setDatetimeSend($row['datetimeSend']);
                $schedule->setDatetimeEnd($row['datetimeEnd']);
                $data[] = $schedule;
            }
        }
        return $data;
    }
}