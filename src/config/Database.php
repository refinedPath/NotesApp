<?php
declare(strict_types=1);

// src/config/Database.php

class Database
{
    private ?PDO $connection = null;

    public function __construct()
    {
      $env = parse_ini_file(__DIR__ . '/../../.env');
      
      if ($env === false) {
        echo "Cannot read database configuration file.";
        return;
      }

      try {
        $DSN = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
        $this->connection = new PDO($DSN, $env['DB_USER'], $env['DB_PASSWORD'], [
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
