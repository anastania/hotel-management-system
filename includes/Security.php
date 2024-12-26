<?php

class Security {
    public static function init() {
        // Initialize CSRF protection
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Set security headers
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    public static function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                Logger::error('CSRF token validation failed');
                header('HTTP/1.1 403 Forbidden');
                die('Invalid CSRF token');
            }
        }
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateRandomToken() {
        return bin2hex(random_bytes(32));
    }

    public static function rateLimitCheck($key, $limit = 5, $timeWindow = 300) {
        $attempts = $_SESSION['rate_limit'][$key] ?? [];
        $attempts = array_filter($attempts, function($timestamp) use ($timeWindow) {
            return $timestamp > time() - $timeWindow;
        });
        
        if (count($attempts) >= $limit) {
            Logger::error('Rate limit exceeded for key: ' . $key);
            return false;
        }

        $attempts[] = time();
        $_SESSION['rate_limit'][$key] = $attempts;
        return true;
    }
}
