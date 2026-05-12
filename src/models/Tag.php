<?php
declare(strict_types=1);

// src/models/Tag.php

class Tag
{
  // properties
  private PDO $connection;
  private string $tagsTable = 'tags';
  private string $noteTagsTable = 'note_tags';

  // constructor
  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
  }

  // create(string $name): int
  // Inserts a tag, returns the new tag's ID
  public function create(string $name): int
  {
    $stmt = $this->connection->prepare(
      "INSERT INTO {$this->tagsTable} (name) 
      VALUES (:name)"
    );

    $stmt->execute([
      ':name' => $name,
    ]);

    return (int) $this->connection->lastInsertId();
  }

  // getAll(): array
  // Returns all tags, ordered by name ASC
  /** @return array<int, array<string, mixed>> */
  public function getAll(): array
  {
    $stmt = $this->connection->prepare(
      "SELECT * FROM {$this->tagsTable} ORDER BY name ASC"
    );

    $stmt->execute();

    return $stmt->fetchAll();
  }

  // getById(int $id): ?array
  // Returns one tag by ID, or null if not found
  /** @return array<string, mixed>|null */
  public function getById(int $id): ?array
  {
    $stmt = $this->connection->prepare(
      "SELECT * FROM {$this->tagsTable} WHERE id = :id"
    );

    $stmt->execute([
      ':id' => $id,
    ]);

    return $stmt->fetch() ?: null;
  }

  // update(int $id, string $name): bool
  // Updates a tag, returns true/false
  public function update(int $id, string $name): bool
  {
    $stmt = $this->connection->prepare(
      "UPDATE {$this->tagsTable} SET name = :name WHERE id = :id"
    );

    return $stmt->execute([
      ':id' => $id,
      ':name' => $name,
    ]);
  }

  // delete(int $id): bool
  // Deletes a tag, returns true/false
  public function delete(int $id): bool
  {
    $stmt = $this->connection->prepare(
      "DELETE FROM {$this->tagsTable} WHERE id = :id"
    );

    return $stmt->execute([
      ':id' => $id,
    ]);
  }

  // assignToNote(int $tagId, int $noteId): bool
  // Assigns a tag to a note
  public function assignToNote(int $tagId, int $noteId): bool
  {
    $stmt = $this->connection->prepare(
      "INSERT IGNORE INTO {$this->noteTagsTable} (note_id, tag_id)
      VALUES (:noteId, :tagId)"
    );

    return $stmt->execute([
      ':noteId' => $noteId,
      ':tagId' => $tagId,
    ]);
  }

  // removeFromNote(int $tagId, int $noteId): bool
  // Removes a tag from a note
  public function removeFromNote(int $tagId, int $noteId): bool
  {
    $stmt = $this->connection->prepare(
      "DELETE FROM {$this->noteTagsTable} WHERE tag_id = :tagId AND note_id = :noteId"
    );

    return $stmt->execute([
      ':tagId' => $tagId,
      ':noteId' => $noteId,
    ]);
  }

  // getTagsByNoteId(int $noteId): array
  // Returns all tags assigned to a note
  /** @return array<int, array<string, mixed>> */
  public function getTagsByNoteId(int $noteId): array
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