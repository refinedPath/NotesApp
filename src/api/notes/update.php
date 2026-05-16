<?php

declare(strict_types=1);

// src/api/notes/update.php

header("Content-Type: application/json");

$noteDefaultBackground = '#212529';

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== "PUT") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use PUT.']);
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

// Validate title
$title = trim($payloadJson['title'] ?? '');
if (empty($title)) {
  http_response_code(400);
  echo json_encode(['error' => 'Title is required.']);
  exit;
}

// Set defaults for optional fields
$content = trim($payloadJson['content'] ?? '');
$color = trim($payloadJson['color'] ?? $noteDefaultBackground);
$isPinned = $payloadJson['isPinned'] ?? false;

// Create DB connection and Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$note = new Note($connection);

// Call update(), return JSON response with try/catch
try {
  $noteExists = $note->getById($noteId);

  if ($noteExists !== null) {
    $note->update($noteId, $title, $content, $color, $isPinned);

    echo json_encode(['success' => "Note '{$title}' was updated."]);
  } else {
    http_response_code(404);
    echo json_encode(['error' => "Cannot update note. Note with ID {$noteId} not found."]);
  }
} catch (Exception $e) {
  http_response_code(500);

  if (Config::get('APP_DEBUG') === "true") {
    echo json_encode(['error' => "Cannot update note with ID {$noteId}. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot update note with ID {$noteId}."]);
  }
}
