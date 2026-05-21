<?php

declare(strict_types=1);

// src/api/notes/pin.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== "PUT") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use PUT.']);
  exit;
}

// Validate Note ID
$noteId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($noteId === null || $noteId <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Note ID is required.']);
  exit;
}

// Read JSON body
if (empty($rawBody = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Request body is empty.']);
  exit;
}

$requestData = json_decode($rawBody, true);
if ($requestData === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Malformed JSON data.']);
  exit;
}

// Validate Pin / Unpin state
if (!array_key_exists('is_pinned', $requestData) || !is_bool($requestData['is_pinned'])) {
  http_response_code(400);
  echo json_encode(['error' => 'is_pinned is required and must be a boolean.']);
  exit;
}

$isPinned = $requestData['is_pinned'];

// Connect to database and Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$noteModel = new Note($connection);

// Call setPinned(), return JSON response with try/catch
try {
  $existingNote = $noteModel->getById($noteId);

  if ($existingNote !== null) {
    $noteModel->setPinned($noteId, $isPinned);

    $updatedNote = $noteModel->getById($noteId);

    echo json_encode(['success' => $updatedNote]);
  } else {
    http_response_code(404);
    echo json_encode(['error' => "Note with ID {$noteId} not found."]);
  }
} catch (Throwable $e) {
  http_response_code(500);

  if (Config::getBool('APP_DEBUG')) {
    echo json_encode(['error' => "Cannot pin note with ID {$noteId}. Database error message: {$e->getMessage()}"]);
  } else {
    echo json_encode(['error' => "Cannot pin note with ID {$noteId}."]);
  }
}
