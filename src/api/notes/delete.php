<?php

declare(strict_types=1);

// src/api/notes/delete.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
  Response::error('Method not allowed. Must use DELETE.', 405);
}

// Validate note ID
$noteId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($noteId === null || $noteId <= 0) {
  Response::error('Note ID is required.');
}

try {
  // Connect to database and create Note model
  $db = new Database();
  $connection = $db->getConnection();

  $noteModel = new Note($connection);

  $existingNote = $noteModel->getById($noteId);

  if ($existingNote !== null) {
    $noteModel->delete($noteId);

    Response::noContent();
  } else {
    Response::error("Cannot delete note. Note with ID {$noteId} not found.", 404);
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot delete note with ID {$noteId}. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error("Cannot delete note with ID {$noteId}.", 500);
  }
}
