<?php

declare(strict_types=1);

// src/models/NoteTag.php

class NoteTag
{
  private PDO $connection;
  private string $noteTagsTable = 'note_tags';
  private string $tagsTable = 'tags';

  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
  }

  // Return all junction rows joined with tag attributes
  /** @return array<int, array<string, mixed>> */
  public function getAllNoteTagPairs(): array
  {
    $stmt = $this->connection->prepare(
      "SELECT {$this->noteTagsTable}.note_id, {$this->tagsTable}.id, {$this->tagsTable}.name, {$this->tagsTable}.color
        FROM {$this->noteTagsTable}
        JOIN {$this->tagsTable} ON {$this->tagsTable}.id = {$this->noteTagsTable}.tag_id
        ORDER BY {$this->noteTagsTable}.note_id, {$this->tagsTable}.name"
    );

    $stmt->execute();

    return $stmt->fetchAll();
  }

  // Assigns a tag to a note, returns affected row count
  public function assignToNote(int $tagId, int $noteId): int
  {
    $stmt = $this->connection->prepare(
      "INSERT IGNORE INTO {$this->noteTagsTable} (note_id, tag_id)
      VALUES (:noteId, :tagId)"
    );

    $stmt->execute([
      ':noteId' => $noteId,
      ':tagId' => $tagId,
    ]);

    return $stmt->rowCount();
  }

  // Removes a tag from a note, returns affected row count
  public function removeFromNote(int $tagId, int $noteId): int
  {
    $stmt = $this->connection->prepare(
      "DELETE FROM {$this->noteTagsTable} WHERE tag_id = :tagId AND note_id = :noteId"
    );

    $stmt->execute([
      ':tagId' => $tagId,
      ':noteId' => $noteId,
    ]);

    return $stmt->rowCount();
  }

  // Returns all tags assigned to a note
  /** @return array<int, array<string, mixed>> */
  public function tagsForNote(int $noteId): array
  {
    $stmt = $this->connection->prepare(
      "SELECT {$this->tagsTable}.* FROM {$this->tagsTable} 
        JOIN {$this->noteTagsTable} ON {$this->tagsTable}.id = {$this->noteTagsTable}.tag_id
        WHERE {$this->noteTagsTable}.note_id = :noteId
        ORDER BY {$this->tagsTable}.name"
    );

    $stmt->execute([
      ':noteId' => $noteId,
    ]);

    return $stmt->fetchAll();
  }
}
