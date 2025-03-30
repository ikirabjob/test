<?php

declare(strict_types=1);

namespace App\Utils;

use JetBrains\PhpStorm\NoReturn;
use JsonException;

final class Response
{
    /**
     * @throws JsonException
     */
    #[NoReturn] public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit();
    }

    /**
     * @throws JsonException
     */
    public static function error(string $message, int $statusCode = 400): void {
        self::json(['error' => $message], $statusCode);
    }

    /**
     * @throws JsonException
     */
    public static function success(array $data, int $statusCode = 200): void {
        self::json(['data' => $data], $statusCode);
    }
}