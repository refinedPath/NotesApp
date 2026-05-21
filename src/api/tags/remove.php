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

// Create DB connection and tag and note models
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$tag = new Tag($connection);
$note = new Note($connection);

// Call removeFromNote(), return JSON response with try/catch
try {
  $tagExists = $tag->getById($tagId);
  $noteExists = $note->getById($noteId);

  if ($tagExists === null) {
    http_response_code(404);
    echo json_encode(['error' => "Cannot remove tag from note. Tag with ID {$tagId} not found."]);
    exit;
  }

  if ($noteExists === null) {
    http_response_code(404);
    echo json_encode(['error' => "Cannot remove tag from note. Note with ID {$noteId} not found."]);
    exit;
  }

  $removedTag = $tag->removeFromNote($tagId, $noteId);

  echo json_encode(['success' => "Removed tag '{$tagExists['name']}' from note '{$noteExists['title']}'."]);
} catch (Exception $e) {
  http_response_code(500);

  if (Config::getBool('APP_DEBUG')) {
    echo json_encode(['error' => "Cannot remove tag with ID {$tagId} from note with ID {$noteId}. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot remove tag with ID {$tagId} from note with ID {$noteId}."]);
  }
}
