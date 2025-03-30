<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Utils\Response;
use DateMalformedStringException;
use JsonException;

final class EventController
{
    private Event $eventModel;
    private EventRegistration $registrationModel;
    private AuthMiddleware $authMiddleware;

    public function __construct() {
        $this->eventModel = new Event();
        $this->registrationModel = new EventRegistration();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * @throws JsonException
     */
    public function getEvents(): void {
        $limit = (int)($_GET['limit'] ?? 10);
        $offset = (int)($_GET['offset'] ?? 0);

        $events = $this->eventModel->findUpcomingEvents($limit, $offset);

        Response::success(['events' => $events]);
    }

    /**
     * @throws JsonException
     */
    public function getEvent(int $id): void {
        $event = $this->eventModel->findById($id);

        if ($event === null) {
            Response::error('Event not found', 404);
        }

        // Check if event is private
        if (!$event['is_public']) {
            // Only allow creator or admin to view
            $payload = $this->authMiddleware->authenticate();

            if ($payload === null || ($payload['userId'] !== $event['creator_id'] && $payload['role'] !== 'admin')) {
                Response::error('Unauthorized: Access to this event is restricted', 403);
            }
        }

        // Get registration count
        $registrationCount = $this->eventModel->getRegistrationCount($id);
        $event['registration_count'] = $registrationCount;

        Response::success(['event' => $event]);
    }

    /**
     * @throws DateMalformedStringException
     * @throws JsonException
     */
    public function createEvent(): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        // Validate required fields
        $required = ['title', 'start_date', 'end_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Missing required field: $field");
            }
        }

        // Validate dates
        $startDate = new \DateTimeImmutable($data['start_date']);
        $endDate = new \DateTimeImmutable($data['end_date']);

        if ($startDate > $endDate) {
            Response::error('Start date must be before end date');
        }

        // Create event
        $eventData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'capacity' => $data['capacity'] ?? null,
            'is_public' => $data['is_public'] ?? true,
            'creator_id' => $payload['userId']
        ];

        $eventId = $this->eventModel->create($eventData);

        if (!$eventId) {
            Response::error('Failed to create event', 500);
        }

        // Add categories if provided
        if (!empty($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {
                $this->eventModel->addCategory($eventId, (int)$categoryId);
            }
        }

        $event = $this->eventModel->findById($eventId);
        Response::success(['event' => $event], 201);
    }

    /**
     * @throws JsonException
     * @throws DateMalformedStringException
     */
    public function updateEvent(int $id): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        // Get existing event
        $event = $this->eventModel->findById($id);

        if ($event === null) {
            Response::error('Event not found', 404);
        }

        // Check if user is authorized to update (must be creator or admin)
        if ($payload['userId'] !== $event['creator_id'] && $payload['role'] !== 'admin') {
            Response::error('Unauthorized: You cannot modify this event', 403);
        }

        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        // Validate dates if provided
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $startDate = new \DateTime($data['start_date']);
            $endDate = new \DateTime($data['end_date']);

            if ($startDate > $endDate) {
                Response::error('Start date must be before end date');
            }
        }

        // Update allowed fields
        $allowedFields = [
            'title', 'description', 'location', 'start_date',
            'end_date', 'capacity', 'is_public'
        ];

        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            Response::error('No valid fields to update');
        }

        $success = $this->eventModel->update($id, $updateData);

        if (!$success) {
            Response::error('Failed to update event', 500);
        }

        // Update categories if provided
        if (isset($data['categories']) && is_array($data['categories'])) {
            // First, remove all existing categories (inefficient but simple)
            $query = "DELETE FROM event_categories WHERE event_id = :event_id";
            $stmt = $this->eventModel->db->prepare($query);
            $stmt->bindParam(':event_id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            // Then add new categories
            foreach ($data['categories'] as $categoryId) {
                $this->eventModel->addCategory($id, (int)$categoryId);
            }
        }

        $updatedEvent = $this->eventModel->findById($id);
        Response::success(['event' => $updatedEvent]);
    }

    /**
     * @throws JsonException
     */
    public function deleteEvent(int $id): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        // Get existing event
        $event = $this->eventModel->findById($id);

        if ($event === null) {
            Response::error('Event not found', 404);
        }

        // Check if user is authorized to delete (must be creator or admin)
        if ($payload['userId'] !== $event['creator_id'] && $payload['role'] !== 'admin') {
            Response::error('Unauthorized: You cannot delete this event', 403);
        }

        $success = $this->eventModel->delete($id);

        if (!$success) {
            Response::error('Failed to delete event', 500);
        }

        Response::success(['message' => 'Event deleted successfully']);
    }

    /**
     * @throws JsonException
     */
    public function registerForEvent(int $eventId): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        // Check if event exists
        $event = $this->eventModel->findById($eventId);

        if ($event === null) {
            Response::error('Event not found', 404);
        }

        // Check if user is already registered
        if ($this->registrationModel->isUserRegistered($eventId, $payload['userId'])) {
            Response::error('You are already registered for this event', 400);
        }

        // Register user
        $registrationId = $this->registrationModel->registerUserForEvent($eventId, $payload['userId']);

        if (!$registrationId) {
            Response::error('Failed to register for event. The event may be full.', 400);
        }

        Response::success(['message' => 'Successfully registered for event']);
    }

    /**
     * @throws JsonException
     */
    public function cancelRegistration(int $eventId): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        // Check if event exists
        $event = $this->eventModel->findById($eventId);

        if ($event === null) {
            Response::error('Event not found', 404);
        }

        // Check if user is registered
        if (!$this->registrationModel->isUserRegistered($eventId, $payload['userId'])) {
            Response::error('You are not registered for this event', 400);
        }

        // Cancel registration
        $success = $this->registrationModel->cancelRegistration($eventId, $payload['userId']);

        if (!$success) {
            Response::error('Failed to cancel registration', 500);
        }

        Response::success(['message' => 'Registration cancelled successfully']);
    }

    /**
     * @throws JsonException
     */
    public function getRegisteredUsers(int $eventId): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        // Get event
        $event = $this->eventModel->findById($eventId);

        if ($event === null) {
            Response::error('Event not found', 404);
        }

        // Check if user is authorized (must be creator or admin)
        if ($payload['userId'] !== $event['creator_id'] && $payload['role'] !== 'admin') {
            Response::error('Unauthorized: You cannot view attendees for this event', 403);
        }

        // Get attendees
        $attendees = $this->registrationModel->getEventAttendees($eventId);

        Response::success(['attendees' => $attendees]);
    }

    /**
     * @throws JsonException
     */
    public function markAttendance(int $eventId, int $userId): void {
        $payload = $this->authMiddleware->requireAdmin();

        if ($payload === null) {
            return;
        }

        // Check if event exists
        $event = $this->eventModel->findById($eventId);

        if ($event === null) {
            Response::error('Event not found', 404);
        }

        // Check if user is registered
        if (!$this->registrationModel->isUserRegistered($eventId, $userId)) {
            Response::error('User is not registered for this event', 400);
        }

        // Mark attendance
        $success = $this->registrationModel->markAttended($eventId, $userId);

        if (!$success) {
            Response::error('Failed to mark attendance', 500);
        }

        Response::success(['message' => 'Attendance marked successfully']);
    }

    /**
     * @throws JsonException
     */
    public function searchEvents(): void {
        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 10);
        $offset = (int)($_GET['offset'] ?? 0);

        if (empty($query)) {
            Response::error('Search query is required');
        }

        $events = $this->eventModel->searchEvents($query, $limit, $offset);

        Response::success(['events' => $events]);
    }
}