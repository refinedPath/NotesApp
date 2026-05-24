<?php

declare(strict_types=1);

// src/config/Database.php

require_once __DIR__ . '/Config.php';

class Database
{
  private PDO $connection;

  public function __construct()
  {
    $dsn = "mysql:host=" . Config::get('DB_HOST') . ";dbname=" . Config::get('DB_NAME') . ";charset=utf8mb4";
    $this->connection = new PDO($dsn, Config::get('DB_USER'), Config::get('DB_PASSWORD'), [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
  }

  public function getConnection(): PDO
  {
    return $this->connection;
  }
}
