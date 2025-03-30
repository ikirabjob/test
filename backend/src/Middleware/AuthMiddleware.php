<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Enums\UserRole;
use App\Utils\JWT;
use App\Utils\Response;
use Couchbase\Role;
use JsonException;

final class AuthMiddleware
{
    private JWT $jwt;

    public function __construct() {
        $this->jwt = new JWT();
    }

    /**
     * @throws JsonException
     */
    public function authenticate(): ?array {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            Response::error('No token provided', 401);
            return null;
        }

        $token = $matches[1];
        $payload = $this->jwt->validate($token);

        if ($payload === null) {
            Response::error('Invalid or expired token', 401);
            return null;
        }

        return $payload;
    }

    /**
     * @throws JsonException
     */
    public function requireAdmin(): ?array {
        $payload = $this->authenticate();

        if ($payload === null) {
            return null;
        }

        if ($payload['role'] !== UserRole::ADMIN->value) {
            Response::error('Unauthorized: Admin access required', 403);
            return null;
        }

        return $payload;
    }
}