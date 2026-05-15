<?php
declare(strict_types=1);

// src/api/tags/read.php

header("Content-Type: application/json");

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is GET
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed. Must use GET.']);
  exit;
}

// Validate ID
$queriedTagId = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Create DB connection and tag model
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  http_response_code(500);
  echo json_encode(['error' => 'Cannot connect to database.']);
  exit;
}

$tag = new Tag($connection);

if ($queriedTagId !== null) { // querying a tag by ID
  $queriedTag = $tag->getById($queriedTagId);

  if ($queriedTag === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Tag not found.']);
  } else {
    echo json_encode(['success' => $queriedTag]);
  }
} else {  // Querying all Tags
  echo json_encode(['success' => $tag->getAll()]);
}
