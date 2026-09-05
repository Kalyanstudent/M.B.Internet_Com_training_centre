<?php
/**
 * SkillPoint Structured JSON Response Helper
 */

class Response {
    public static function json(bool $success, string $message, mixed $data = null, array $errors = [], int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        $payload = [
            'success' => $success,
            'message' => $message
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void {
        self::json(true, $message, $data, [], $statusCode);
    }

    public static function error(string $message = 'An error occurred', array $errors = [], int $statusCode = 400): void {
        self::json(false, $message, null, $errors, $statusCode);
    }

    public static function unauthorized(string $message = 'Authentication required'): void {
        self::error($message, [], 401);
    }

    public static function forbidden(string $message = 'Access denied. You do not have permission to perform this action'): void {
        self::error($message, [], 403);
    }

    public static function notFound(string $message = 'Resource not found'): void {
        self::error($message, [], 404);
    }
}
