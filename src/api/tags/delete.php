<?php

declare(strict_types=1);

// src/api/tags/delete.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
  Response::error('Method not allowed. Must use DELETE.', 405);
}

// Validate Tag ID
$tagId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($tagId === null || $tagId <= 0) {
  Response::error('Tag ID is required.');
}

try {
  // Connect to database and create Tag model
  $db = new Database();
  $connection = $db->getConnection();

  $tagModel = new Tag($connection);

  $existingTag = $tagModel->getById($tagId);

  if ($existingTag !== null) {
    $tagModel->delete($tagId);

    Response::noContent();
  } else {
    Response::error("Cannot delete tag. Tag with ID {$tagId} not found.", 404);
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot delete tag with ID {$tagId}. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error("Cannot delete tag with ID {$tagId}.", 500);
  }
}
