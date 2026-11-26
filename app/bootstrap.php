<?php
declare(strict_types=1);
function config(): array {
    static $config;
    return $config ??= require dirname(__DIR__).'/config/local.php';
}
function db(): PDO {
    static $db;
    $cfg = config();
    return $db ??= new PDO($cfg['dsn'], $cfg['db_user'], $cfg['db_password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
function query(string $sql, array $params = []): PDOStatement {
    $q = db()->prepare($sql);
    $q->execute($params);
    return $q;
}
function e(mixed $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function go(string $page): never {
    header('Location: ?'.$page, true, 303);
    exit;
}
function jsonData(array $data): string {
    return json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
}
class InputError extends RuntimeException {}
