<?php

declare(strict_types=1);

// src/api/tags/update.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  Response::error('Method not allowed. Must use PUT.', 405);
}

// Validate Tag ID
$tagId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($tagId === null || $tagId <= 0) {
  Response::error('Tag ID is required.');
}

// Read JSON body
if (empty($rawBody = file_get_contents('php://input'))) {
  Response::error('Request body is empty.');
}

$requestData = json_decode($rawBody, true);
if ($requestData === null) {
  Response::error('Malformed JSON data.');
}

// Validate tag name
$name = mb_trim($requestData['name'] ?? '');
if ($name === '') {
  Response::error('Tag name is required.');
}
if (mb_strlen($name) > Tag::MAX_NAME_LENGTH) {
  Response::error(sprintf('Tag name cannot exceed %d characters.', Tag::MAX_NAME_LENGTH));
}

try {
  // Connect to database and create Tag model
  $db = new Database();
  $connection = $db->getConnection();

  $tagModel = new Tag($connection);

  $existingTag = $tagModel->getById($tagId);

  if ($existingTag !== null) {
    $tagModel->update($tagId, $name);

    $updatedTag = $tagModel->getById($tagId);

    Response::success($updatedTag);
  } else {
    Response::error("Cannot update tag. Tag with ID {$tagId} not found.", 404);
  }
} catch (PDOException $e) {
  if ($e->getCode() === '23000') {
    if (Config::getBool('APP_DEBUG')) {
      Response::error("Tag '{$name}' already exists. Try another name. Database error message: {$e->getMessage()}.");
    } else {
      Response::error("Tag '{$name}' already exists. Try another name.");
    }
  } else {
    if (Config::getBool('APP_DEBUG')) {
      Response::error("Cannot update tag. Database error message: {$e->getMessage()}.", 500);
    } else {
      Response::error('Cannot update tag.', 500);
    }
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot update tag. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error('Cannot update tag.', 500);
  }
}
