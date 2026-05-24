<?php

declare(strict_types=1);

// src/api/tags/read.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  Response::error('Method not allowed. Must use GET.', 405);
}

// Validate tag ID
$tagId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($tagId !== null && $tagId <= 0) {
  Response::error('Tag ID must be a positive integer.');
}

// Validate Note ID
$noteId = isset($_GET['noteId']) ? (int) $_GET['noteId'] : null;
if ($noteId !== null && $noteId <= 0) {
  Response::error('Note ID must be a positive integer.');
}

// Error if both Tag ID and Note ID present
if ($tagId !== null && $noteId !== null) {
  Response::error('Cannot use both id and noteId parameters. Use one at a time.');
}

// Connect to database and create tag model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  Response::error('Cannot connect to database.', 500);
}

$tagModel = new Tag($connection);

try {
  if ($noteId !== null) { // querying all tags that belong to a note and exiting
    Response::success($tagModel->getTagsByNoteId($noteId));
  } elseif ($tagId !== null) { // querying a tag by ID
    $existingTag = $tagModel->getById($tagId);

    if ($existingTag === null) {
      Response::error("Tag with ID {$tagId} not found.", 404);
    } else {
      Response::success($existingTag);
    }
  } else {  // Querying all Tags
    Response::success($tagModel->getAll());
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot read tags. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error('Cannot read tags.', 500);
  }
}
