<?php
declare(strict_types=1);

// src/api/tags/read.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is GET
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use GET.']);
  exit;
}

// Validate tag ID
$queriedTagId = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Check if we have Note ID and validate it
$queriedNoteId = isset($_GET['noteId']) ? (int) $_GET['noteId'] : null;

// Error if both Tag ID and Note ID present
if ($queriedTagId !== null && $queriedNoteId !== null) {
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

$tag = new Tag($connection);

if ($queriedNoteId !== null) { // querying all tags that belong to a note and exiting
  echo json_encode(['success' => $tag->getTagsByNoteId($queriedNoteId)]);
  exit;
}

if ($queriedTagId !== null) { // querying a tag by ID
  $queriedTag = $tag->getById($queriedTagId);

  if ($queriedTag === null) {
    http_response_code(404);
    echo json_encode(['error' => "Tag with ID {$queriedTagId} not found."]);
  } else {
    echo json_encode(['success' => $queriedTag]);
  }
} else {  // Querying all Tags
  echo json_encode(['success' => $tag->getAll()]);
}
