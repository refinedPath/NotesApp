<?php

declare(strict_types=1);

// src/models/Tag.php

class Tag
{
  public const int MAX_NAME_LENGTH = 100;
  public const int MAX_COLOR_LENGTH = 7;
  public const string DEFAULT_COLOR = '#7B8389';

  private PDO $connection;
  private string $tagsTable = 'tags';
  private string $noteTagsTable = 'note_tags';

  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
  }

  // Inserts a tag, returns the new tag's ID
  public function create(string $name, string $color): int
  {
    $stmt = $this->connection->prepare(
      "INSERT INTO {$this->tagsTable} (name, color) 
      VALUES (:name, :color)"
    );

    $stmt->execute([
      ':name' => $name,
      ':color' => $color,
    ]);

    return (int) $this->connection->lastInsertId();
  }

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

  // Updates a tag, returns affected row count
  public function update(int $id, string $name, string $color): int
  {
    $stmt = $this->connection->prepare(
      "UPDATE {$this->tagsTable} SET name = :name, color = :color WHERE id = :id"
    );

    $stmt->execute([
      ':id' => $id,
      ':name' => $name,
      ':color' => $color,
    ]);

    return $stmt->rowCount();
  }

  // Deletes a tag, returns affected row count
  public function delete(int $id): int
  {
    $stmt = $this->connection->prepare(
      "DELETE FROM {$this->tagsTable} WHERE id = :id"
    );

    $stmt->execute([
      ':id' => $id,
    ]);

    return $stmt->rowCount();
  }
}
