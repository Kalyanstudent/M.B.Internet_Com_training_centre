<?php
/**
 * MB Internet And Digital Studio | Strict Input Validation & Sanitization Framework
 */

class Validator {
    /**
     * Context-safe string sanitization (prevents XSS)
     */
    public static function sanitize(mixed $data): mixed {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        if (is_string($data)) {
            return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
        }
        return $data;
    }

    /**
     * Strip HTML tags from plain text inputs
     */
    public static function cleanString(?string $str): string {
        if ($str === null) return '';
        return trim(strip_tags($str));
    }

    /**
     * Standard RFC 5322 Email Validation
     */
    public static function isValidEmail(string $email): bool {
        return (bool) filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    /**
     * Indian 10-Digit Mobile Number Validation (Starts with 6, 7, 8, 9)
     */
    public static function isValidIndianMobile(string $mobile): bool {
        $clean = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            $clean = substr($clean, 2);
        }
        return (bool) preg_match('/^[6-9]\d{9}$/', $clean);
    }

    /**
     * Normalize Indian Mobile Number to standard 10 digits
     */
    public static function normalizeMobile(string $mobile): string {
        $clean = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            $clean = substr($clean, 2);
        }
        return $clean;
    }

    /**
     * Indian Income Tax PAN Card Format (5 letters + 4 digits + 1 letter)
     */
    public static function isValidPAN(string $pan): bool {
        return (bool) preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i', trim($pan));
    }

    /**
     * Indian GSTIN Format (15 chars)
     */
    public static function isValidGSTIN(string $gstin): bool {
        return (bool) preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i', trim($gstin));
    }

    /**
     * Validate integer ID
     */
    public static function isValidId(mixed $id): bool {
        return filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) !== false;
    }

    /**
     * Validate length bounds
     */
    public static function isLengthBetween(string $str, int $min, int $max): bool {
        $len = mb_strlen(trim($str), 'UTF-8');
        return ($len >= $min && $len <= $max);
    }

    /**
     * Check multiple required fields
     */
    public static function validateRequired(array $data, array $requiredFields): array {
        $errors = [];
        foreach ($requiredFields as $field => $label) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $errors[$field] = "{$label} is required.";
            }
        }
        return $errors;
    }
}
