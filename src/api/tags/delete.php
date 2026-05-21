<?php

declare(strict_types=1);

// src/api/tags/delete.php

header("Content-Type: application/json");

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
$tagId = isset($requestData['id']) ? (int) $requestData['id'] : null;
if ($tagId === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Tag ID is required.']);
  exit;
}

// Create DB connection and Tag model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$tag = new Tag($connection);

// Call delete(), return JSON response with try/catch
try {
  $tagExists = $tag->getById($tagId);

  if ($tagExists !== null) {
    $tag->delete($tagId);

    echo json_encode(['success' => "Tag '{$tagExists['name']}' was deleted."]);
  } else {
    http_response_code(404);
    echo json_encode(['error' => "Cannot delete tag. Tag with ID {$tagId} not found."]);
  }
} catch (Exception $e) {
  http_response_code(500);

  if (Config::getBool('APP_DEBUG')) {
    echo json_encode(['error' => "Cannot delete tag with ID {$tagId}. Database error message: {$e->getMessage()}."]);
  } else {
    echo json_encode(['error' => "Cannot delete tag with ID {$tagId}."]);
  }
}
