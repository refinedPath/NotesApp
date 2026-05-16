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

// Validate ID
$queryNoteId = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Check if we have Tag ID
$queryTagId = isset($_GET['tagId']) ? (int) $_GET['tagId']: null;

// Error if both Note ID and Tag ID present
if ($queryNoteId !== null && $queryTagId !== null) {
  http_response_code(400);
  echo json_encode(['error' => 'Cannot use both id and tagId parameters. Use one at a time.']);
  exit;
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

if ($queryTagId !== null) { // querying all notes that belong to a tag and exiting
  echo json_encode(['success' => $note->getByTagId($queryTagId)]);
  exit;
}

if ($queryNoteId !== null) {  // querying a note by ID
  $queriedNote = $note->getById($queryNoteId);

  if ($queriedNote === null) {
    http_response_code(404);
    echo json_encode(['error' => "Note with ID {$queryNoteId} not found."]);
  } else {
    echo json_encode(['success' => $queriedNote]);
  }
} else {  // querying all notes
  echo json_encode(['success' => $note->getAll()]);
}
