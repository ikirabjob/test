<?php

declare(strict_types=1);

namespace App\Models;

use App\src\Enums\UserRole;

final class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array {
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public function createUser(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        UserRole $role = UserRole::USER
    ): int {
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $data = [
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role
        ];

        return $this->create($data);
    }

    public function updatePassword(int $userId, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    public function isAdmin(int $userId): bool {
        $user = $this->findById($userId);
        return $user !== null && $user['role'] === UserRole::ADMIN->value;
    }
}