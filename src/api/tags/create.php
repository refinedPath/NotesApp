<?php
declare(strict_types=1);

// src/api/tags/create.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is POST
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use POST.']);
  exit;
}

// Read JSON body
if (empty($payload = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON.']);
  exit;
}

$payloadJson = json_decode($payload, true);

// Validate name
$name = trim($payloadJson['name'] ?? '');
if (empty($name)) {
  http_response_code(400);
  echo json_encode(['error' => 'Name is required.']);
  exit;
}

// Create DB connection and Tag model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$tag = new Tag($connection);

// Call create(), return JSON response with try/catch
try
{
  $newTagId = $tag->create($name);

  echo json_encode(['success' => "Created new tag with ID {$newTagId}."]);
} catch (Exception $e) {
  if ($e->getCode() === '23000') {
    http_response_code(400);
    if (Config::get('APP_DEBUG') === "true") {
      echo json_encode(['error' => "Tag \"{$name}\" already exists. Try another name. Database error message: {$e->getMessage()}."]);
    } else {
      echo json_encode(['error' => "Tag \"{$name}\" already exists. Try another name."]);
    }
  } else {
    http_response_code(500);
    if (Config::get('APP_DEBUG') === "true") {
      echo json_encode(['error' => "Cannot create new tag: {$e->getMessage()}."]);
    } else {
      echo json_encode(['error' => "Cannot create new tag."]);
    }
  }
}
