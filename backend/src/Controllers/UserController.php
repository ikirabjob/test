<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use JsonException;

final class UserController
{
    private Event $eventModel;
    private AuthMiddleware $authMiddleware;

    public function __construct() {
        $this->eventModel = new Event();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * @throws JsonException
     */
    public function getUserEvents(): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        $limit = (int)($_GET['limit'] ?? 10);
        $offset = (int)($_GET['offset'] ?? 0);

        $events = $this->eventModel->findEventsByUser($payload['userId'], $limit, $offset);

        Response::success(['events' => $events]);
    }

    /**
     * @throws JsonException
     */
    public function getCreatedEvents(): void {
        $payload = $this->authMiddleware->authenticate();

        if ($payload === null) {
            return;
        }

        $limit = (int)($_GET['limit'] ?? 10);
        $offset = (int)($_GET['offset'] ?? 0);

        $events = $this->eventModel->findEventsByCreator($payload['userId'], $limit, $offset);

        Response::success(['events' => $events]);
    }
}