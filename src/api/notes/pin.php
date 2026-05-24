<?php

declare(strict_types=1);

// src/api/notes/pin.php

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

// Validate Pin / Unpin state
if (!array_key_exists('is_pinned', $requestData) || !is_bool($requestData['is_pinned'])) {
  Response::error('is_pinned is required and must be a boolean.');
}

$isPinned = $requestData['is_pinned'];

// Connect to database and create Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  Response::error('Cannot connect to database.', 500);
}

$noteModel = new Note($connection);

// Call setPinned(), return JSON response with try/catch
try {
  $existingNote = $noteModel->getById($noteId);

  if ($existingNote !== null) {
    $noteModel->setPinned($noteId, $isPinned);

    $updatedNote = $noteModel->getById($noteId);

    Response::success($updatedNote);
  } else {
    Response::error("Note with ID {$noteId} not found.", 404);
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot pin note with ID {$noteId}. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error("Cannot pin note with ID {$noteId}.", 500);
  }
}
