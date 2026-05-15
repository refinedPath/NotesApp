<?php
declare(strict_types=1);

// src/api/notes/update.php

header("Content-Type: application/json");

$noteDefaultBackground = '#212529';

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== "PUT") {
  http_response_code(405);
  echo json_encode(['error' => 'Method Not Allowed. Must use PUT.']);
  exit;
}

// Read JSON body
if (empty($payload = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON']);
  exit;
}

$payloadJson = json_decode($payload, true);

// Validate ID
$noteId = isset($payloadJson['id']) ? (int) $payloadJson['id'] : null;

// Validate title
$title = trim($payloadJson['title'] ?? '');
if (empty($title)) {
  http_response_code(400);
  echo json_encode(['error' => 'Title is required']);
  exit;
}

// Set defaults for optional fields
$content = trim($payloadJson['content'] ?? '');
$color = trim($payloadJson['color'] ?? $noteDefaultBackground);
$isPinned = $payloadJson['isPinned'] ?? false;

// Create DB connection and Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database']);
  exit;
}

$note = new Note($connection);

// Call update(), return JSON response with try/catch
if ($noteId !== null) {
  try
  {
    $note->update($noteId, $title, $content, $color, $isPinned);

    echo json_encode(['success' => "Note with ID {$noteId} was updated"]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Cannot update note with ID {$noteId}"]);
  }
} else {
  http_response_code(400);
  echo json_encode(['error' => 'Note ID is required']);
}
