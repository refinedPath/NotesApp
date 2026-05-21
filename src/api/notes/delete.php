<?php

declare(strict_types=1);

// src/api/notes/delete.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is DELETE
if ($_SERVER['REQUEST_METHOD'] !== "DELETE") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use DELETE.']);
  exit;
}

// Validate note ID
$noteId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($noteId === null || $noteId <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Note ID is required.']);
  exit;
}

// Create DB connection and Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$noteModel = new Note($connection);

// Call delete(), return JSON response with try/catch
try {
  $existingNote = $noteModel->getById($noteId);

  if ($existingNote !== null) {
    $noteModel->delete($noteId);

    echo json_encode(['success' => ['id' => $noteId, 'deleted' => true]]);
  } else {
    http_response_code(404);
    echo json_encode(['error' => "Cannot delete note. Note with ID {$noteId} not found."]);
  }
} catch (Throwable $e) {
  http_response_code(500);

  if (Config::getBool('APP_DEBUG')) {
    echo json_encode(['error' => "Cannot delete note with ID {$noteId}. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot delete note with ID {$noteId}."]);
  }
}
