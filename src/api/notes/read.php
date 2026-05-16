<?php

declare(strict_types=1);

// src/api/notes/read.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is GET
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use GET.']);
  exit;
}

// Validate note ID, Tag ID, search, sorting, and sort order and validate them
$noteId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$tagId = isset($_GET['tagId']) ? (int) $_GET['tagId'] : null;
$search = isset($_GET['search']) ? (trim($_GET['search'])) : null;
$sortBy = $_GET['sortBy'] ?? 'created_at';
$orderDirection = $_GET['orderDirection'] ?? 'DESC';

// Error if both Note ID and Tag ID present
if ($noteId !== null && $tagId !== null) {
  http_response_code(400);
  echo json_encode(['error' => 'Cannot use both id and tagId parameters. Use one at a time.']);
  exit;
}

// Error if Note ID and ( search or sort ) present at the same time
if ($noteId !== null && (isset($_GET['search']) || isset($_GET['sortBy']) || isset($_GET['orderDirection']))) {
  http_response_code(400);
  echo json_encode(['error' => 'Note ID must be the only parameter. Cannot use it with search, sortBy, or orderDirection.']);
  exit;
}

// Check search criteria >= 2 and <= 255 characters long
if (isset($search)) {
  $searchCharsCount = mb_strlen($search);
  if ($searchCharsCount < 2 || $searchCharsCount > 255) {
    http_response_code(400);
    echo json_encode(['error' => 'Search criteria must be between 2 and 255 characters long.']);
    exit;
  }
}

// Create DB connection and Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$note = new Note($connection);

if ($noteId !== null) {  // querying a note by ID
  $queriedNote = $note->getById($noteId);

  if ($queriedNote === null) {
    http_response_code(404);
    echo json_encode(['error' => "Note with ID {$noteId} not found."]);
  } else {
    echo json_encode(['success' => $queriedNote]);
  }
} elseif ($tagId !== null && $search !== null) {  // searching all notes that contain search criteria and belong to a tag
  echo json_encode(['success' => $note->searchByTagId($tagId, $search, $sortBy, $orderDirection)]);
} elseif ($tagId !== null) { // searching all notes that belong to a tag and don't have search criteria
  echo json_encode(['success' => $note->getByTagId($tagId, $sortBy, $orderDirection)]);
} elseif ($search !== null) { //  search by search criteria
  echo json_encode(['success' => $note->search($search, $sortBy, $orderDirection)]);
} else {  // querying all notes
  echo json_encode(['success' => $note->getAll($sortBy, $orderDirection)]);
}
