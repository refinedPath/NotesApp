<?php

declare(strict_types=1);

// src/api/tags/remove.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
  Response::error('Method not allowed. Must use DELETE.', 405);
}

// Validate Tag ID
$tagId = isset($_GET['tagId']) ? (int) $_GET['tagId'] : null;
if ($tagId === null || $tagId <= 0) {
  Response::error('Tag ID is required.');
}

// Validate Note ID
$noteId = isset($_GET['noteId']) ? (int) $_GET['noteId'] : null;
if ($noteId === null || $noteId <= 0) {
  Response::error('Note ID is required.');
}

try {
  // Connect to database and create Tag and Note models
  $db = new Database();
  $connection = $db->getConnection();

  $tagModel = new Tag($connection);
  $noteModel = new Note($connection);

  $existingTag = $tagModel->getById($tagId);
  $existingNote = $noteModel->getById($noteId);

  if ($existingTag === null) {
    Response::error("Cannot remove tag. Tag with ID {$tagId} not found.", 404);
  }

  if ($existingNote === null) {
    Response::error("Cannot remove tag. Note with ID {$noteId} not found.", 404);
  }

  $tagModel->removeFromNote($tagId, $noteId);

  Response::success(['tagId' => $tagId, 'noteId' => $noteId, 'removed' => true]);
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot remove tag with ID {$tagId} from note with ID {$noteId}. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error("Cannot remove tag with ID {$tagId} from note with ID {$noteId}.", 500);
  }
}
