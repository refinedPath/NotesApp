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
if (empty($rawBody = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON.']);
  exit;
}

$requestData = json_decode($rawBody, true);

// Validate title
$title = mb_trim($requestData['title'] ?? '');
if (empty($title)) {
  http_response_code(400);
  echo json_encode(['error' => 'Title is required.']);
  exit;
}
if (mb_strlen($title) > 255) {
  http_response_code(400);
  echo json_encode(['error' => 'Title cannot exceed 255 characters.']);
  exit;
}

// Set defaults for optional fields
$content = mb_trim($requestData['content'] ?? '');
if (mb_strlen($content) > 5000) {
  http_response_code(400);
  echo json_encode(['error' => 'Content cannot exceed 5000 characters.']);
  exit;
}

$color = mb_trim($requestData['color'] ?? $noteDefaultBackground);
$isPinned = filter_var(
  $requestData['is_pinned'] ?? false,
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

$noteModel = new Note($connection);

// Call create(), return JSON response with try/catch
try {
  $newNoteId = $noteModel->create($title, $content, $color, $isPinned);

  $newNote = $noteModel->getById($newNoteId);

  echo json_encode(['success' => $newNote]);
} catch (Throwable $e) {
  http_response_code(500);

  if (Config::getBool('APP_DEBUG')) {
    echo json_encode(['error' => "Cannot create new note. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot create new note."]);
  }
}
