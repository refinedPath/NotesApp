<?php

declare(strict_types=1);

// src/api/notes/create.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  Response::error('Method not allowed. Must use POST.', 405);
}

// Read JSON body
if (empty($rawBody = file_get_contents('php://input'))) {
  Response::error('Request body is empty.');
}

$requestData = json_decode($rawBody, true);
if ($requestData === null) {
  Response::error('Malformed JSON data.');
}

// Validate title
$title = mb_trim($requestData['title'] ?? '');
if ($title === '') {
  Response::error('Note title is required.');
}
if (mb_strlen($title) > Note::MAX_TITLE_LENGTH) {
  Response::error(sprintf('Note title cannot exceed %d characters.', Note::MAX_TITLE_LENGTH));
}

// Set defaults for optional fields
$content = mb_trim($requestData['content'] ?? '');
if (mb_strlen($content) > Note::MAX_CONTENT_LENGTH) {
  Response::error(sprintf('Note content cannot exceed %d characters.', Note::MAX_CONTENT_LENGTH));
}
if ($content === '') {
  $content = null;
}

$color = mb_trim($requestData['color'] ?? Note::DEFAULT_COLOR);
if (!Validator::isHexColor($color)) {
  Response::error('Note color must be in hex format (#RRGGBB).');
}

// If is_pinned not set, default to not pinned, if set, validate as boolean
if (array_key_exists('is_pinned', $requestData) && !is_bool($requestData['is_pinned'])) {
  Response::error('is_pinned must be a boolean.');
}
$isPinned = $requestData['is_pinned'] ?? false;

try {
  // Connect to database and create Note model
  $db = new Database();
  $connection = $db->getConnection();

  $noteModel = new Note($connection);

  $newNoteId = $noteModel->create($title, $content, $color, $isPinned);

  $newNote = $noteModel->getById($newNoteId);

  Response::success($newNote, 201);
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot create new note. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error('Cannot create new note.', 500);
  }
}
