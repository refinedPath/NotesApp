<?php

declare(strict_types=1);

// src/config/Config.php

class Config
{
  /** @var array<string, string> */
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

  public static function getBool(string $property, bool $default = false): bool
  {
    $value = Config::get($property);
    if ($value === null) {
      return $default;
    }

    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
  }
}
