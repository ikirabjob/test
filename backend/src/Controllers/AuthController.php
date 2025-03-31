<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Utils\JWT;
use App\Utils\Response;
use JsonException;

final class AuthController
{
    private User $userModel;
    private JWT $jwt;

    public function __construct() {
        $this->userModel = new User();
        $this->jwt = new JWT();
    }

    /**
     * @throws JsonException
     */
    public function register(): void {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        // Validate input
        $required = ['email', 'password', 'firstName', 'lastName'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Missing required field: $field");
            }
        }

        // Check if email already exists
        if ($this->userModel->findByEmail($data['email']) !== null) {
            Response::error('Email already registered', 409);
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::error('Invalid email format');
        }

        // Validate password
        if (strlen($data['password']) < 8) {
            Response::error('Password must be at least 8 characters long');
        }

        // Create user
        $userId = $this->userModel->createUser(
            $data['email'],
            $data['password'],
            $data['firstName'],
            $data['lastName'],
            UserRole::USER // Default to 'user' role
        );

        if (!$userId) {
            Response::error('Failed to create user', 500);
        }

        // Generate JWT
        $user = $this->userModel->findById($userId);
        $token = $this->jwt->generate([
            'userId' => $userId,
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        Response::success([
            'token' => $token,
            'user' => [
                'id' => $userId,
                'email' => $user['email'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'role' => $user['role']
            ]
        ], 201);
    }

    /**
     * @throws JsonException
     */
    public function login(): void {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        // Validate input
        if (empty($data['email']) || empty($data['password'])) {
            Response::error('Email and password are required');
        }

        // Find user by email
        $user = $this->userModel->findByEmail($data['email']);

        if ($user === null) {
            Response::error('Invalid email or password', 401);
        }

        // Verify password
        if (!password_verify($data['password'], $user['password'])) {
            Response::error('Invalid email or password', 401);
        }

        // Generate JWT
        $token = $this->jwt->generate([
            'userId' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        Response::success([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'role' => $user['role']
            ]
        ]);
    }

    /**
     * @throws JsonException
     */
    public function getCurrentUser(): void {
        // Get the token from the Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            Response::error('No token provided', 401);
        }

        $token = $matches[1];
        $payload = $this->jwt->validate($token);

        if ($payload === null) {
            Response::error('Invalid or expired token', 401);
        }

        // Get user details
        $user = $this->userModel->findById($payload['userId']);

        if ($user === null) {
            Response::error('User not found', 404);
        }

        Response::success([
            'id' => $user['id'],
            'email' => $user['email'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'role' => $user['role']
        ]);
    }
}