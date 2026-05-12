<?php
declare(strict_types=1);

// src/models/Note.php

class Note
{
  // private properties
  private PDO $connection;
  private string $table = 'notes';

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
      "INSERT INTO {$this->table} (title, content, color, is_pinned) 
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
      "SELECT * FROM {$this->table} ORDER BY is_pinned DESC, created_at DESC"
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
      "SELECT * FROM {$this->table} WHERE id = :id"
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
      "UPDATE {$this->table} SET title = :title, content = :content, color = :color, is_pinned = :isPinned WHERE id = :id"
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
      "DELETE FROM {$this->table} WHERE id = :id"
    );

    return $stmt->execute([
      ':id' => $id
    ]);
  }
}
