<?php

declare(strict_types=1);

// src/lib/Response.php

class Response
{
  public static function success(mixed $data, int $statusCode = 200): never
  {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode(['success' => $data], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    exit;
  }

  public static function error(string $message, int $statusCode = 400): never
  {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode(['error' => $message], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    exit;
  }

  public static function noContent(): never
  {
    http_response_code(204);
    exit;
  }
}
