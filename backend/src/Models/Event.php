<?php

declare(strict_types=1);

namespace App\Models;

final class Event extends BaseModel
{
    protected string $table = 'events';

    public function findUpcomingEvents(int $limit = 10, int $offset = 0): array {
        $query = "SELECT * FROM $this->table 
                 WHERE start_date > NOW() AND is_public = TRUE
                 ORDER BY start_date ASC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findEventsByCategory(int $categoryId, int $limit = 10, int $offset = 0): array {
        $query = "SELECT e.* FROM {$this->table} e
                 JOIN event_categories ec ON e.id = ec.event_id
                 WHERE ec.category_id = :category_id AND e.is_public = TRUE
                 ORDER BY e.start_date ASC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findEventsByUser(int $userId, int $limit = 10, int $offset = 0): array {
        $query = "SELECT e.* FROM {$this->table} e
                 JOIN event_registrations er ON e.id = er.event_id
                 WHERE er.user_id = :user_id
                 ORDER BY e.start_date ASC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findEventsByCreator(int $creatorId, int $limit = 10, int $offset = 0): array {
        return $this->findAll(
            ['creator_id' => $creatorId],
            ['start_date' => 'ASC'],
            $limit,
            $offset
        );
    }

    public function searchEvents(string $query, int $limit = 10, int $offset = 0): array {
        $searchTerm = "%{$query}%";

        $sql = "SELECT * FROM {$this->table} 
               WHERE (title ILIKE :search OR description ILIKE :search OR location ILIKE :search)
               AND is_public = TRUE
               ORDER BY start_date ASC
               LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':search', $searchTerm, \PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRegistrationCount(int $eventId): int {
        $query = "SELECT COUNT(*) FROM event_registrations WHERE event_id = :event_id AND status != 'cancelled'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function addCategory(int $eventId, int $categoryId): bool {
        $query = "INSERT INTO event_categories (event_id, category_id) VALUES (:event_id, :category_id)
                 ON CONFLICT (event_id, category_id) DO NOTHING";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, \PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $categoryId, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function removeCategory(int $eventId, int $categoryId): bool {
        $query = "DELETE FROM event_categories WHERE event_id = :event_id AND category_id = :category_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, \PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $categoryId, \PDO::PARAM_INT);

        return $stmt->execute();
    }

}