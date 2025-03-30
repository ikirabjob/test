<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;

final class EventRegistration extends BaseModel
{
    protected string $table = 'event_registrations';

    /**
     * @throws \Exception
     */
    public function registerUserForEvent(int $eventId, int $userId): int|false {
        try {
            $this->beginTransaction();

            // Check if event has capacity
            $eventModel = new Event();
            $event = $eventModel->findById($eventId);

            if ($event === null) {
                $this->rollback();
                return false;
            }

            // Check capacity if set
            if ($event['capacity'] > 0) {
                $currentRegistrations = $eventModel->getRegistrationCount($eventId);

                if ($currentRegistrations >= $event['capacity']) {
                    $this->rollback();
                    return false; // Event is full
                }
            }

            // Create registration
            $registrationId = $this->create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'status' => 'registered'
            ]);

            $this->commit();
            return $registrationId;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function cancelRegistration(int $eventId, int $userId): bool {
        $query = "UPDATE $this->table SET status = {EventStatus::CANCELLED->value}
                 WHERE event_id = :event_id AND user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function markAttended(int $eventId, int $userId): bool {
        $query = "UPDATE $this->table SET status = {EventStatus::ATTENDED->value}
                 WHERE event_id = :event_id AND user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function isUserRegistered(int $eventId, int $userId): bool {
        $query = "SELECT COUNT(*) FROM $this->table
                 WHERE event_id = :event_id AND user_id = :user_id AND status = {EventStatus::REGISTERED->value}";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public function getEventAttendees(int $eventId): array {
        $query = "SELECT u.id, u.first_name, u.last_name, u.email, er.registration_date, er.status 
                 FROM {$this->table} er
                 JOIN users u ON er.user_id = u.id
                 WHERE er.event_id = :event_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}