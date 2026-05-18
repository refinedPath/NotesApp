<?php

declare(strict_types=1);

// src/api/notes/create.php

header("Content-Type: application/json");

$noteDefaultBackground = '#212529';

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use POST.']);
  exit;
}

// Read JSON body
if (empty($payload = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON.']);
  exit;
}

$payloadJson = json_decode($payload, true);

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
$isPinned = filter_var(
  $payloadJson['is_pinned'] ?? false,
  FILTER_VALIDATE_BOOLEAN,
  FILTER_NULL_ON_FAILURE
) ?? false;

// Create DB connection and Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$note = new Note($connection);

// Call create(), return JSON response with try/catch
try {
  $newNoteId = $note->create($title, $content, $color, $isPinned);

  $newNote = $note->getById($newNoteId);

  echo json_encode(['success' => $newNote]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => "Cannot create new note: {$e->getMessage()}."]);
}
