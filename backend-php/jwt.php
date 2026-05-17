<?php
// JWT helper

class JWT {
    private static $secret = 'local_secret';

    public static function init() {
        self::$secret = getenv('JWT_SECRET') ?: 'local_secret';
    }

    public static function encode($data) {
        self::init();
        $header = base64_encode(json_encode(['type' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode($data));
        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", self::$secret, true));
        return "$header.$payload.$signature";
    }

    public static function decode($token) {
        self::init();
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        $signature = base64_encode(hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", self::$secret, true));
        if ($parts[2] !== $signature) return null;

        return json_decode(base64_decode($parts[1]), true);
    }

    public static function getToken() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer (.+)/', $auth, $m)) return null;
        return self::decode($m[1]);
    }

    public static function required() {
        $user = self::getToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        return $user;
    }
}
