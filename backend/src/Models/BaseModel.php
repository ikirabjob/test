<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use Exception;

abstract class BaseModel
{
    public \PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->db = (new Database)->getConnection();
    }

    public function findById(int $id): ?array {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public function findAll(array $conditions = [], array $orderBy = [], int $limit = 0, int $offset = 0): array {
        $query = "SELECT * FROM {$this->table}";

        // Handle conditions
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "{$key} = :{$key}";
            }
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Handle ordering
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $column => $direction) {
                $orderClauses[] = "{$column} {$direction}";
            }
            $query .= " ORDER BY " . implode(', ', $orderClauses);
        }

        // Handle limit and offset
        if ($limit > 0) {
            $query .= " LIMIT {$limit}";
            if ($offset > 0) {
                $query .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($query);

        // Bind condition values
        foreach ($conditions as $key => $value) {
            $paramType = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue(":{$key}", $value, $paramType);
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders}) RETURNING {$this->primaryKey}";
        $stmt = $this->db->prepare($query);

        foreach ($data as $key => $value) {
            $paramType = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue(":{$key}", $value, $paramType);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $setClauses = [];
        foreach (array_keys($data) as $key) {
            $setClauses[] = "{$key} = :{$key}";
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

        foreach ($data as $key => $value) {
            $paramType = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue(":{$key}", $value, $paramType);
        }

        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function beginTransaction(): void {
        $this->db->beginTransaction();
    }

    public function commit(): void {
        $this->db->commit();
    }

    public function rollback(): void {
        $this->db->rollBack();
    }
}