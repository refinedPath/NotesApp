<?php

declare(strict_types=1);

// src/api/notes/read.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  Response::error('Method not allowed. Must use GET.', 405);
}

// Validate Note ID, Tag ID, search, sorting, and sort order
$noteId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($noteId !== null && $noteId <= 0) {
  Response::error('Note ID must be a positive integer.');
}

$tagId = isset($_GET['tagId']) ? (int) $_GET['tagId'] : null;
if ($tagId !== null && $tagId <= 0) {
  Response::error('Tag ID must be a positive integer.');
}

$search = isset($_GET['search']) ? mb_trim($_GET['search']) : null;
$sortBy = $_GET['sortBy'] ?? 'created_at';
$orderDirection = $_GET['orderDirection'] ?? 'DESC';

// Error if both Note ID and Tag ID present
if ($noteId !== null && $tagId !== null) {
  Response::error('Cannot use both id and tagId parameters. Use one at a time.');
}

// Error if Note ID and ( search or sort ) present at the same time
if ($noteId !== null && (isset($_GET['search']) || isset($_GET['sortBy']) || isset($_GET['orderDirection']))) {
  Response::error('Note ID must be the only parameter. Cannot use it with search, sortBy, or orderDirection.');
}

// Check search criteria >= 2 and <= 255 characters long
if ($search !== null) {
  $searchCharsCount = mb_strlen($search);
  if ($searchCharsCount < 2 || $searchCharsCount > 255) {
    Response::error('Search criteria must be between 2 and 255 characters long.');
  }
}

// Connect to database and create Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  Response::error('Cannot connect to database.', 500);
}

$noteModel = new Note($connection);

try {
  if ($noteId !== null) {  // querying a note by ID
    $existingNote = $noteModel->getById($noteId);

    if ($existingNote === null) {
      Response::error("Note with ID {$noteId} not found.", 404);
    } else {
      Response::success($existingNote);
    }
  } elseif ($tagId !== null && $search !== null) {  // searching all notes that contain search criteria and belong to a tag
    Response::success($noteModel->searchByTagId($tagId, $search, $sortBy, $orderDirection));
  } elseif ($tagId !== null) { // searching all notes that belong to a tag and don't have search criteria
    Response::success($noteModel->getByTagId($tagId, $sortBy, $orderDirection));
  } elseif ($search !== null) { //  search by search criteria
    Response::success($noteModel->search($search, $sortBy, $orderDirection));
  } else {  // querying all notes
    Response::success($noteModel->getAll($sortBy, $orderDirection));
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot read notes. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error('Cannot read notes.', 500);
  }
}
