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

$requestBody = json_decode($rawBody, true);
if ($requestBody === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Empty or malformed JSON data.']);
  exit;
}

// Validate tag ID
$tagId = isset($requestBody['tagId']) ? (int) $requestBody['tagId'] : null;
if ($tagId === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Tag ID is required.']);
  exit;
}

// Validate note ID
$noteId = isset($requestBody['noteId']) ? (int) $requestBody['noteId'] : null;
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

// Call assign(), return JSON response with try/catch
try
{
  $assignedTag = $tag->assignToNote($tagId, $noteId);

  echo json_encode(['success' => "Assigned tag with ID {$tagId} to note with ID {$noteId}."]);
} catch (Exception $e) {
  http_response_code(500);

  if (Config::get('APP_DEBUG') === "true") {
    echo json_encode(['error' => "Cannot assign tag with ID {$tagId} to note with ID {$noteId}. Database error message: {$e->getMessage()}."]);
  } else {
    http_response_code(500);
    echo json_encode(['error' => "Cannot assign tag with ID {$tagId} to note with ID {$noteId}."]);
  }
}
