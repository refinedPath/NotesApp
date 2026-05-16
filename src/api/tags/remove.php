<?php

declare(strict_types=1);

// src/api/tags/remove.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is DELETE
if ($_SERVER['REQUEST_METHOD'] !== "DELETE") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use DELETE.']);
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

// Create DB connection and tag model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$tag = new Tag($connection);

// Call removeFromNote(), return JSON response with try/catch
try {
  $removedTag = $tag->removeFromNote($tagId, $noteId);

  echo json_encode(['success' => "Removed tag with ID {$tagId} from note with ID {$noteId}."]);
} catch (Exception $e) {
  http_response_code(500);

  if (Config::get('APP_DEBUG') === "true") {
    echo json_encode(['error' => "Cannot remove tag with ID {$tagId} from note with ID {$noteId}. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot remove tag with ID {$tagId} from note with ID {$noteId}."]);
  }
}
