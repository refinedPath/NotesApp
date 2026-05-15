<?php
declare(strict_types=1);

// src/api/notes/delete.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check requiest method is DELETE
if ($_SERVER['REQUEST_METHOD'] !== "DELETE") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use DELETE.']);
  exit;
}

// Read JSON body
if (empty($payload = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON.']);
  exit;
}

$payloadJson = json_decode($payload, true);

// Validate ID
$noteId = isset($payloadJson['id']) ? (int) $payloadJson['id'] : null;

// Create DB connection and Note model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$note = new Note($connection);

// Call delete(), return JSON response with try/catch
if ($noteId !== null) {
  try
  {
    $note->delete($noteId);

    echo json_encode(['success' => "Note with ID {$noteId} was deleted."]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Cannot delete note with ID {$noteId}."]);
  }
} else {
  http_response_code(400);
  echo json_encode(['error' => 'Note ID is required.']);
}
