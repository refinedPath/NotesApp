<?php

declare(strict_types=1);

// src/api/tags/update.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== "PUT") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use PUT.']);
  exit;
}

// Read JSON body
if (empty($rawBody = file_get_contents('php://input'))) {
  http_response_code(400);
  echo json_encode(['error' => 'Bad request or malformed JSON.']);
  exit;
}

$requestData = json_decode($rawBody, true);

// Validate tag ID
$tagId = isset($requestData['id']) ? (int) $requestData['id'] : null;
if ($tagId === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Tag ID is required.']);
  exit;
}

// Validate name
$name = mb_trim($requestData['name'] ?? '');
if (empty($name)) {
  http_response_code(400);
  echo json_encode(['error' => 'Tag name is required.']);
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

$tagModel = new Tag($connection);

// Call update(), return JSON response with try/catch
try {
  $tagExists = $tagModel->getById($tagId);

  if ($tagExists !== null) {
    $tagModel->update($tagId, $name);

    echo json_encode(['success' => "Tag '{$name}' was updated."]);
  } else {
    http_response_code(404);
    echo json_encode(['error' => "Cannot update tag. Tag with ID {$tagId} not found."]);
  }
} catch (Exception $e) {
  if ($e->getCode() === '23000') {
    http_response_code(400);

    if (Config::getBool('APP_DEBUG')) {
      echo json_encode(['error' => "Tag '{$name}' already exists. Try another name. Database error message: {$e->getMessage()}."]);
    } else {
      echo json_encode(['error' => "Tag '{$name}' already exists. Try another name."]);
    }
  } else {
    if (Config::getBool('APP_DEBUG')) {
      http_response_code(500);
      echo json_encode(['error' => "Cannot update tag: {$e->getMessage()}."]);
    } else {
      http_response_code(500);
      echo json_encode(['error' => 'Cannot update tag.']);
    }
  }
}
