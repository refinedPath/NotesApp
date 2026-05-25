<?php

declare(strict_types=1);

// src/api/note_tags/read.php

require_once __DIR__ . '/../../bootstrap.php';

// Check request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  Response::error('Method not allowed. Must use GET.', 405);
}

try {
  $db = new Database();
  $connection = $db->getConnection();

  $noteTagsModel = new NoteTag($connection);

  $noteTagsPairs = $noteTagsModel->getAllNoteTagPairs();

  $groupedNotes = [];
  foreach ($noteTagsPairs as $noteRow) {
    if (!array_key_exists($noteRow['note_id'], $groupedNotes)) {
      $groupedNotes[$noteRow['note_id']]['note_id'] = $noteRow['note_id'];
    }
    $groupedNotes[$noteRow['note_id']]['tags'][] = [
      'id' => $noteRow['id'],
      'name' => $noteRow['name'],
      'color' => $noteRow['color'],
    ];
  }

  Response::success(array_values($groupedNotes));
} catch (Throwable $e) {
  if (Config::getBool('APP_DEBUG')) {
    Response::error("Cannot read note tag assignments. Database error message: {$e->getMessage()}.", 500);
  } else {
    Response::error('Cannot read note tag assignments.', 500);
  }
}
