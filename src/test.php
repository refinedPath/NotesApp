<?php
declare(strict_types=1);

// src/test.php

$tableNames = [
  'notesTable' => "notes",
  'tagsTable' => "tags",
  'noteTagsTable' => "note_tags",
];

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Note.php';
require_once __DIR__ . '/models/Tag.php';

// Connect
$db = new Database();
$connection = $db->getConnection();

if ($connection === null) {
  echo "Failed to connect to database.\n";
  exit;
}

$connection->exec("SET FOREIGN_KEY_CHECKS = 0");
$connection->exec("TRUNCATE TABLE {$tableNames['noteTagsTable']}");
$connection->exec("TRUNCATE TABLE {$tableNames['tagsTable']}");
$connection->exec("TRUNCATE TABLE {$tableNames['notesTable']}");
$connection->exec("SET FOREIGN_KEY_CHECKS = 1");

$note = new Note($connection);
$tag = new Tag($connection);

// Test 1: Create a note
$noteId = $note->create('My first note', 'This is a test note.', '#ff5733', false);
echo "Created note with ID {$noteId}\n";

// Test 2: Create tags
$tagId1 = $tag->create('urgent');
$tagId2 = $tag->create('personal');
echo "Created tags with IDs {$tagId1} and {$tagId2}.\n";

// Test 3: Assign tags to note
$tag->assignToNote($tagId1, $noteId);
$tag->assignToNote($tagId2, $noteId);
echo "Tags assigned to note.\n";

// Test 4: Get all notes
$allNotes = $note->getAll();
echo "All notes:\n";
print_r($allNotes);

// Test 5: Get note by ID
$singleNote = $note->getById($noteId);
echo "Single note:\n";
print_r($singleNote);

// Test 6: Get tags for note
$noteTags = $tag->getTagsByNoteId($noteId);
echo "Tags for note {$noteId}:\n";
print_r($noteTags);

// Test 7: Update note after a pause
sleep(3);
$note->update($noteId, 'Updated title', 'Updated content.', '#33ff57', true);
$updated = $note->getById($noteId);
echo "Updated note:\n";
print_r($updated);

// Test 8: Remove tag from note
$tag->removeFromNote($tagId1, $noteId);
$remainingTags = $tag->getTagsByNoteId($noteId);
echo "Tags after removal:\n";
print_r($remainingTags);

// Test 9: Delete tag
$tag->delete($tagId1);
$deletedTag = $tag->getById($tagId1);
$tag->delete($tagId2);
$deletedTag = $tag->getById($tagId2);
echo "Deleted tag (should be null): ";
var_dump($deletedTag);

// Test 10: Delete note (should cascade note_tags)
$note->delete($noteId);
$deleted = $note->getById($noteId);
echo "Deleted note (should be null): ";
var_dump($deleted);
