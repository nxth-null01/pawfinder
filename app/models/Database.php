<?php
class Database {
  private static ?PDO $pdo = null;
  public static function connect(array $config): void {
    if (self::$pdo) return;
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
  }
  public static function pdo(): PDO { return self::$pdo; }
}
