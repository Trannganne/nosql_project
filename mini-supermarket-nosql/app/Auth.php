<?php
declare(strict_types=1);

namespace App;

final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $user = Database::connection()->users->findOne(['username' => $username, 'active' => true]);
        if (!$user || !password_verify($password, (string) $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (string) $user['_id'],
            'code' => (string) $user['code'],
            'name' => (string) $user['name'],
            'username' => (string) $user['username'],
            'role' => (string) $user['role'],
        ];
        return true;
    }

    public static function check(): bool { return isset($_SESSION['user']); }
    public static function user(): array { return $_SESSION['user'] ?? []; }
    public static function isAdmin(): bool { return (self::user()['role'] ?? '') === 'admin'; }
    public static function logout(): void { $_SESSION = []; session_destroy(); }
}
