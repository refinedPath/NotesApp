<?php

declare(strict_types=1);

// src/api/notes/update.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  Response::error('Method not allowed. Must use PUT.', 405);
}

// Validate Note ID
$noteId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($noteId === null || $noteId <= 0) {
  Response::error('Note ID is required.');
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

// Validate optional fields
$content = mb_trim($requestData['content'] ?? '');
if (mb_strlen($content) > Note::MAX_CONTENT_LENGTH) {
  Response::error(sprintf('Note content cannot exceed %d characters.', Note::MAX_CONTENT_LENGTH));
}
if ($content === '') {
  $content = null;
}

$color = mb_trim($requestData['color'] ?? Note::DEFAULT_COLOR);
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
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

  $existingNote = $noteModel->getById($noteId);

  if ($existingNote !== null) {
    $noteModel->update($noteId, $title, $content, $color, $isPinned);

    $updatedNote = $noteModel->getById($noteId);

    Response::success($updatedNote);
  } else {
    Response::error("Cannot update note. Note with ID {$noteId} not found.", 404);
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot update note with ID {$noteId}. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error("Cannot update note with ID {$noteId}.", 500);
  }
}
