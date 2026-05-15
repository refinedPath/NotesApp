<?php
declare(strict_types=1);

// src/config/Config.php

class Config
{
  private static array $envFile = [];

  public static function get(string $property): ?string
  {
    if (empty(Config::$envFile)) {
      $result = parse_ini_file(__DIR__ . '/../../.env');

      if ($result === false) {
        echo "Cannot read '.env' configuration file.";
        return null;
      }

      Config::$envFile = $result;
    }

    return isset(Config::$envFile[$property]) ? Config::$envFile[$property] : null;
  }
}
