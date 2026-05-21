<?php

declare(strict_types=1);

// src/bootstrap.php

/* Set internal character encoding to UTF-8 */
mb_internal_encoding("UTF-8");

require_once __DIR__ . '/lib/polyfills.php';
require_once __DIR__ . '/lib/Response.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Note.php';
require_once __DIR__ . '/models/Tag.php';
