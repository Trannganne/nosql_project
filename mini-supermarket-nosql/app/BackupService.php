<?php
declare(strict_types=1);

namespace App;

final class BackupService
{
    public function backup(): string
    {
        $dir = $this->backupRoot() . DIRECTORY_SEPARATOR . 'backup_' . date('Ymd_His');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new \RuntimeException('Không tạo được thư mục backup.');
        $command = sprintf('%s --uri=%s --db=%s --out=%s 2>&1',
            escapeshellarg($this->binary('mongodump')),
            escapeshellarg($_ENV['MONGODB_URI'] ?? 'mongodb://127.0.0.1:27017'),
            escapeshellarg($_ENV['MONGODB_DATABASE'] ?? 'mini_supermarket'),
            escapeshellarg($dir)
        );
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) throw new \RuntimeException("Backup thất bại: " . implode("\n", $output));
        return basename($dir);
    }

    public function restore(string $backup): void
    {
        if (!preg_match('/^backup_\d{8}_\d{6}$/', $backup)) throw new \RuntimeException('Tên bản backup không hợp lệ.');
        $path = $this->backupRoot() . DIRECTORY_SEPARATOR . $backup . DIRECTORY_SEPARATOR . ($_ENV['MONGODB_DATABASE'] ?? 'mini_supermarket');
        if (!is_dir($path)) throw new \RuntimeException('Không tìm thấy bản backup.');
        $command = sprintf('%s --uri=%s --db=%s --drop %s 2>&1',
            escapeshellarg($this->binary('mongorestore')),
            escapeshellarg($_ENV['MONGODB_URI'] ?? 'mongodb://127.0.0.1:27017'),
            escapeshellarg($_ENV['MONGODB_DATABASE'] ?? 'mini_supermarket'),
            escapeshellarg($path)
        );
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) throw new \RuntimeException("Restore thất bại: " . implode("\n", $output));
    }

    public function list(): array
    {
        $items = glob($this->backupRoot() . DIRECTORY_SEPARATOR . 'backup_*', GLOB_ONLYDIR) ?: [];
        rsort($items);
        return array_map('basename', $items);
    }

    private function binary(string $name): string
    {
        $base = rtrim((string) ($_ENV['BACKUP_BIN'] ?? ''), '\\/');
        return $base === '' ? $name : $base . DIRECTORY_SEPARATOR . $name . (PHP_OS_FAMILY === 'Windows' ? '.exe' : '');
    }

    private function backupRoot(): string
    {
        $configured = (string) ($_ENV['BACKUP_DIR'] ?? 'storage/backups');
        $root = str_starts_with($configured, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $configured)
            ? $configured : dirname(__DIR__) . DIRECTORY_SEPARATOR . $configured;
        if (!is_dir($root)) mkdir($root, 0775, true);
        return realpath($root) ?: $root;
    }
}
