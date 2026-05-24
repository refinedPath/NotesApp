<?php

declare(strict_types=1);

// src/api/tags/read.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use GET.']);
  exit;
}

// Validate tag ID
$tagId = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Check if we have Note ID and validate it
$noteId = isset($_GET['noteId']) ? (int) $_GET['noteId'] : null;

// Error if both Tag ID and Note ID present
if ($tagId !== null && $noteId !== null) {
  http_response_code(400);
  echo json_encode(['error' => 'Cannot use both id and noteId parameters. Use one at a time.']);
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

$tagModel = new Tag($connection);

if ($noteId !== null) { // querying all tags that belong to a note and exiting
  echo json_encode(['success' => $tagModel->getTagsByNoteId($noteId)]);
  exit;
}

if ($tagId !== null) { // querying a tag by ID
  $existingTag = $tagModel->getById($tagId);

  if ($existingTag === null) {
    http_response_code(404);
    echo json_encode(['error' => "Tag with ID {$tagId} not found."]);
  } else {
    echo json_encode(['success' => $existingTag]);
  }
} else {  // Querying all Tags
  echo json_encode(['success' => $tagModel->getAll()]);
}
