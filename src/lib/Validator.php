<?php

declare(strict_types=1);

// src/lib/Validator.php

final class Validator
{
  public static function isHexColor(string $color): bool
  {
    return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $color);
  }
}
