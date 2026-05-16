<?php

declare(strict_types=1);

// src/models/Note.php

class Note
{
  // private properties
  private PDO $connection;
  private string $notesTable = 'notes';
  private string $noteTagsTable = 'note_tags';

  // constructor
  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
  }

  // create()
  // Inserts a note, returns the new note's ID
  public function create(string $title, string $content, string $color, bool $isPinned): int
  {
    $stmt = $this->connection->prepare(
      "INSERT INTO {$this->notesTable} (title, content, color, is_pinned) 
      VALUES (:title, :content, :color, :isPinned)"
    );

    $stmt->execute([
      ':title' => $title,
      ':content' => $content,
      ':color' => $color,
      ':isPinned' => (int) $isPinned,
    ]);

    return (int) $this->connection->lastInsertId();
  }

  // getAll()
  // Returns all notes, ordered by is_pinned DESC, then created_at DESC
  /** @return array<int, array<string, mixed>> */
  public function getAll(): array
  {
    $stmt = $this->connection->prepare(
      "SELECT * FROM {$this->notesTable} ORDER BY is_pinned DESC, created_at DESC"
    );

    $stmt->execute();

    return $stmt->fetchAll();
  }

  // getById()
  // Returns one note by ID, or null if not found
  /** @return array<string, mixed>|null */
  public function getById(int $id): ?array
  {
    $stmt = $this->connection->prepare(
      "SELECT * FROM {$this->notesTable} WHERE id = :id"
    );

    $stmt->execute([
      ':id' => $id,
    ]);

    return $stmt->fetch() ?: null;
  }

  // update()
  // Updates a note, returns true/false
  public function update(int $id, string $title, string $content, string $color, bool $isPinned): bool
  {
    $stmt = $this->connection->prepare(
      "UPDATE {$this->notesTable} SET title = :title, content = :content, color = :color, is_pinned = :isPinned WHERE id = :id"
    );

    return $stmt->execute([
      ':title' => $title,
      ':content' => $content,
      ':color' => $color,
      ':isPinned' => (int) $isPinned,
      ':id' => $id,
    ]);
  }

  // delete()
  // Deletes a note, returns true/false
  public function delete(int $id): bool
  {
    $stmt = $this->connection->prepare(
      "DELETE FROM {$this->notesTable} WHERE id = :id"
    );

    return $stmt->execute([
      ':id' => $id
    ]);
  }

  // getByTagId()
  // Returns all notes that have a tag
  /** @return array<int, array<string, mixed>> */
  public function getByTagId(int $tagId): array
  {
    $stmt = $this->connection->prepare(
      "SELECT {$this->notesTable}.* FROM {$this->notesTable}
        JOIN {$this->noteTagsTable} ON {$this->notesTable}.id = {$this->noteTagsTable}.note_id
        WHERE {$this->noteTagsTable}.tag_id = :tagId
        ORDER BY {$this->notesTable}.is_pinned DESC, {$this->notesTable}.created_at DESC"
    );

    $stmt->execute([
      ':tagId' => $tagId,
    ]);

    return $stmt->fetchAll();
  }
}
