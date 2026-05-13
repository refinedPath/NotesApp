<?php
declare(strict_types=1);

// src/api/notes/create.php

header("Content-Type: application/json");

$noteDefaultBackground = '#212529';

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Note.php';

// Check request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method Not Allowed. Must use POST.']);
  exit;
}

// Read JSON body
if (empty($payload = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON']);
  exit;
}

$payloadJson = json_decode($payload, true);

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
  echo json_encode(['error' => "Cannot connect to database"]);
  exit;
}

$note = new Note($connection);

// Call create(), return JSON response with try/catch
try
{
  $newNoteId = $note->create($title, $content, $color, $isPinned);

  echo json_encode(['success' => "Created new note with ID {$newNoteId}"]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => "Cannot create new note: {$e->getMessage()}"]);
}
