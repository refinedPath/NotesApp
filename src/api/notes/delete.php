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

// Read JSON body
if (empty($payload = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON.']);
  exit;
}

$payloadJson = json_decode($payload, true);

// Validate note ID
$noteId = isset($payloadJson['id']) ? (int) $payloadJson['id'] : null;
if ($noteId === null) {
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

$note = new Note($connection);

// Call delete(), return JSON response with try/catch
try {
  $noteExists = $note->getById($noteId);

  if ($noteExists !== null) {
    $note->delete($noteId);

    echo json_encode(['success' => "Note '{$noteExists['title']}' was deleted."]);
  } else {
    http_response_code(404);
    echo json_encode(['error' => "Cannot delete note. Note with ID {$noteId} not found."]);
  }
} catch (Exception $e) {
  http_response_code(500);

  if (Config::get('APP_DEBUG') === "true") {
    echo json_encode(['error' => "Cannot delete note with ID {$noteId}. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot delete note with ID {$noteId}."]);
  }
}
