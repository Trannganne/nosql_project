<?php
declare(strict_types=1);

namespace App;

final class Security
{
    public static function csrfToken(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function verifyCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['_token'] ?? ''))) {
            throw new \RuntimeException('Phiên làm việc không hợp lệ. Hãy tải lại trang.');
        }
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
