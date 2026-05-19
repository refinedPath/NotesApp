<?php
declare(strict_types=1);

// src/lib/polyfills.php

/**
 * Polyfill for PHP < 8.4's mb_trim().
 *
 * Strips Unicode whitespace (\pZ) and control characters (\pC) from both
 * ends of the string. Only the default-whitespace behavior is implemented;
 * custom $characters and $encoding arguments are accepted (for signature
 * compatibility) but ignored. Upgrade to PHP 8.4+ for full functionality.
 *
 * @see https://www.php.net/manual/en/function.mb-trim.php
 */
if (!function_exists('mb_trim')) {
  function mb_trim(string $string, ?string $characters = null, ?string $encoding = null): string
  {
    return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
  }
}
