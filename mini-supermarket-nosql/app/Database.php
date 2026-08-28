<?php
declare(strict_types=1);

namespace App;

use MongoDB\Client;
use MongoDB\Database as MongoDatabase;

final class Database
{
    private static ?MongoDatabase $database = null;

    public static function connection(): MongoDatabase
    {
        if (self::$database !== null) {
            return self::$database;
        }

        $uri = $_ENV['MONGODB_URI'] ?? 'mongodb://127.0.0.1:27017';
        $name = $_ENV['MONGODB_DATABASE'] ?? 'mini_supermarket';
        $options = filter_var($_ENV['MONGODB_TLS'] ?? false, FILTER_VALIDATE_BOOL)
            ? ['tls' => true]
            : [];
        self::$database = (new Client($uri, $options))->selectDatabase($name);
        self::$database->command(['ping' => 1]);
        return self::$database;
    }
}
