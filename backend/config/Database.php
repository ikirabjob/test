<?php

declare(strict_types=1);

namespace App\Config;

use Exception;

final class Database
{
    private static ?\PDO $connection = null;
    private readonly string $host;
    private readonly string $database;
    private readonly string $username;
    private readonly string $password;
    private readonly int $port;
    public function __construct(
    ) {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->database = $_ENV['DB_NAME'] ?? 'event_management';
        $this->username = $_ENV['DB_USER'] ?? 'postgres';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'postgres';
        $this->port = (int)($_ENV['DB_PORT'] ?? 5432);
    }

    /**
     * @throws Exception
     */
    public function getConnection(): \PDO {
        if (self::$connection === null) {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";

            try {
                self::$connection = new \PDO(
                    $dsn,
                    $this->username,
                    $this->password,
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
            } catch (\PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}