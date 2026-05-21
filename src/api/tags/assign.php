<?php

declare(strict_types=1);

// src/api/tags/assign.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is POST
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use POST.']);
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

// Validate tag ID
$tagId = isset($requestData['tagId']) ? (int) $requestData['tagId'] : null;
if ($tagId === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Tag ID is required.']);
  exit;
}

// Validate note ID
$noteId = isset($requestData['noteId']) ? (int) $requestData['noteId'] : null;
if ($noteId === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Note ID is required.']);
  exit;
}

// Create DB connection and tag and note models
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$tagModel = new Tag($connection);
$noteModel = new Note($connection);

// Call assignToNote(), return JSON response with try/catch
try {
  $existingTag = $tagModel->getById($tagId);
  $existingNote = $noteModel->getById($noteId);

  if ($existingTag === null) {
    http_response_code(404);
    echo json_encode(['error' => "Cannot assign tag. Tag with ID {$tagId} not found."]);
    exit;
  }

  if ($existingNote === null) {
    http_response_code(404);
    echo json_encode(['error' => "Cannot assign tag. Note with ID {$noteId} not found."]);
    exit;
  }

  $assignedTag = $tagModel->assignToNote($tagId, $noteId);

  echo json_encode(['success' => "Assigned tag '{$existingTag['name']}' to note '{$existingNote['title']}'."]);
} catch (Exception $e) {
  http_response_code(500);

  if (Config::getBool('APP_DEBUG')) {
    echo json_encode(['error' => "Cannot assign tag with ID {$tagId} to note with ID {$noteId}. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot assign tag with ID {$tagId} to note with ID {$noteId}."]);
  }
}
