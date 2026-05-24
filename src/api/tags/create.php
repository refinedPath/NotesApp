<?php

declare(strict_types=1);

// src/api/tags/create.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  Response::error('Method not allowed. Must use POST.', 405);
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

  $newTagId = $tagModel->create($name);

  $newTag = $tagModel->getById($newTagId);

  Response::success($newTag, 201);
} catch (PDOException $e) {
  if ($e->getCode() === '23000') {
    if (Config::getBool('APP_DEBUG')) {
      Response::error("Tag '{$name}' already exists. Try another name. Database error message: {$e->getMessage()}.");
    } else {
      Response::error("Tag '{$name}' already exists. Try another name.");
    }
  } else {
    if (Config::getBool('APP_DEBUG')) {
      Response::error("Cannot create new tag. Database error message: {$e->getMessage()}.", 500);
    } else {
      Response::error('Cannot create new tag.', 500);
    }
  }
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot create new tag. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error('Cannot create new tag.', 500);
  }
}
