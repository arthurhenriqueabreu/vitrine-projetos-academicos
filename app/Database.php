<?php
final class Database {
    private static ?PDO $pdo = null;
    public static function connection(): PDO {
        return self::$pdo ??= new PDO('mysql:host=' . env('DB_HOST', '127.0.0.1') . ';port=' . env('DB_PORT', '3306') . ';dbname=' . env('DB_DATABASE', 'vitrine_academica') . ';charset=utf8mb4', env('DB_USERNAME'), env('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    }
    public static function run(string $sql, array $params = []): PDOStatement { $s = self::connection()->prepare($sql); $s->execute($params); return $s; }
    public static function all(string $sql, array $params = []): array { return self::run($sql, $params)->fetchAll(); }
    public static function one(string $sql, array $params = []): ?array { return self::run($sql, $params)->fetch() ?: null; }
}
