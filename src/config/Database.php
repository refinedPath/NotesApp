<?php
declare(strict_types=1);

// src/config/Database.php

require_once __DIR__ . '/Config.php';

class Database
{
    private ?PDO $connection = null;

    public function __construct()
    {
      try {
        $dsn = "mysql:host=" . Config::get('DB_HOST') . ";dbname=" . Config::get('DB_NAME') . ";charset=utf8mb4";
        $this->connection = new PDO($dsn, Config::get('DB_USER'), Config::get('DB_PASSWORD'), [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $this->connection->exec("SET NAMES 'utf8mb4'");
        $this->connection->exec("SET collation_connection = 'utf8mb4_general_ci'");
        $this->connection->exec("SET character_set_client = 'utf8mb4'");
        $this->connection->exec("SET character_set_results = 'utf8mb4'");
      } catch (PDOException $e) {
        echo "Connection error: " . $e->getMessage();
      }
    }

    public function getConnection(): ?PDO
    {
      return $this->connection;
    }
}
