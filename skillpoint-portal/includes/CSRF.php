<?php
/**
 * SkillPoint CSRF Protection Manager
 */

require_once dirname(__DIR__) . '/config/config.php';

class CSRF {
    public static function getToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(?string $token = null): bool {
        if ($token === null) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
            if (!$token) {
                $rawInput = file_get_contents('php://input');
                if (!empty($rawInput)) {
                    $json = json_decode($rawInput, true);
                    if (is_array($json) && !empty($json['csrf_token'])) {
                        $token = $json['csrf_token'];
                    }
                }
            }
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($sessionToken) || empty($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function verifyOrDie(): void {
        if (!self::validate()) {
            require_once __DIR__ . '/Response.php';
            Response::error('Invalid or expired CSRF security token. Please refresh the page.', [], 403);
        }
    }
}
