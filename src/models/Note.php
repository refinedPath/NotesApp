<?php

declare(strict_types=1);

// src/models/Note.php

class Note
{
  public const int MAX_TITLE_LENGTH = 255;
  public const int MAX_CONTENT_LENGTH = 5000;
  public const int MAX_COLOR_LENGTH = 7;

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
  public function create(string $title, ?string $content, string $color, bool $isPinned): int
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

  // validateSort()
  // Validates sorting and order directions
  /**
   * @return array{string, string}
   */
  private function validateSort(string $sortBy = 'created_at', string $orderDirection = 'DESC'): array
  {
    $allowedSortList = [
      'created_at',
      'updated_at',
      'title',
    ];
    $allowedSortDirections = [
      'ASC',
      'DESC',
    ];

    if (!in_array($sortBy, $allowedSortList, true)) {
      $sortBy = 'created_at';
    }
    if (!in_array($orderDirection, $allowedSortDirections, true)) {
      $orderDirection = 'DESC';
    }

    return [
      $sortBy,
      $orderDirection,
    ];
  }

  // getAll()
  // Returns all notes, ordered by is_pinned DESC, then created_at DESC
  /** @return array<int, array<string, mixed>> */
  public function getAll(string $sortBy = 'created_at', string $orderDirection = 'DESC'): array
  {
    [
      $sortBy,
      $orderDirection,
    ] = $this->validateSort($sortBy, $orderDirection);

    $stmt = $this->connection->prepare(
      "SELECT * FROM {$this->notesTable} ORDER BY is_pinned DESC, $sortBy $orderDirection"
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
  // Updates a note, returns affected row count
  public function update(int $id, string $title, ?string $content, string $color, bool $isPinned): int
  {
    $stmt = $this->connection->prepare(
      "UPDATE {$this->notesTable} SET title = :title, content = :content, color = :color, is_pinned = :isPinned WHERE id = :id"
    );

    $stmt->execute([
      ':title' => $title,
      ':content' => $content,
      ':color' => $color,
      ':isPinned' => (int) $isPinned,
      ':id' => $id,
    ]);

    return $stmt->rowCount();
  }

  // delete()
  // Deletes a note, returns affected row count
  public function delete(int $id): int
  {
    $stmt = $this->connection->prepare(
      "DELETE FROM {$this->notesTable} WHERE id = :id"
    );

    $stmt->execute([
      ':id' => $id
    ]);

    return $stmt->rowCount();
  }

  // setPinned()
  // Sets a note's pinned state, returns rowCount()
  public function setPinned(int $id, bool $isPinned): int
  {
    $stmt = $this->connection->prepare(
      "UPDATE {$this->notesTable} SET is_pinned = :isPinned WHERE id = :id"
    );

    $stmt->execute([
      ':isPinned' => (int) $isPinned,
      ':id' => $id,
    ]);

    return $stmt->rowCount();
  }

  // getByTagId()
  // Returns all notes that have a tag
  /** @return array<int, array<string, mixed>> */
  public function getByTagId(int $tagId, string $sortBy = 'created_at', string $orderDirection = 'DESC'): array
  {
    [
      $sortBy,
      $orderDirection,
    ] = $this->validateSort($sortBy, $orderDirection);

    $stmt = $this->connection->prepare(
      "SELECT {$this->notesTable}.* FROM {$this->notesTable}
        JOIN {$this->noteTagsTable} ON {$this->notesTable}.id = {$this->noteTagsTable}.note_id
        WHERE {$this->noteTagsTable}.tag_id = :tagId
        ORDER BY {$this->notesTable}.is_pinned DESC, {$this->notesTable}.{$sortBy} {$orderDirection}"
    );

    $stmt->execute([
      ':tagId' => $tagId,
    ]);

    return $stmt->fetchAll();
  }

  // search()
  // Return all notes according to search criteria
  /** @return array<int, array<string, mixed>> */
  public function search(string $keyword, string $sortBy = 'created_at', string $orderDirection = 'DESC'): array
  {
    [
      $sortBy,
      $orderDirection,
    ] = $this->validateSort($sortBy, $orderDirection);

    $stmt = $this->connection->prepare(
      "SELECT * FROM {$this->notesTable}
        WHERE
          title LIKE :keyword
          OR
          content LIKE :keyword
        ORDER BY is_pinned DESC, {$sortBy} {$orderDirection}"
    );

    $stmt->execute([
      ':keyword' => '%' . $keyword . '%',
    ]);

    return $stmt->fetchAll();
  }

  // searchByTagId
  // Return all tagged notes according to search criteria
  /** @return array<int, array<string, mixed>> */
  public function searchByTagId(int $tagId, string $keyword, string $sortBy = 'created_at', string $orderDirection = 'DESC'): array
  {
    [
      $sortBy,
      $orderDirection,
    ] = $this->validateSort($sortBy, $orderDirection);

    $stmt = $this->connection->prepare(
      "SELECT {$this->notesTable}.* FROM {$this->notesTable}
        JOIN {$this->noteTagsTable} ON {$this->notesTable}.id = {$this->noteTagsTable}.note_id
        WHERE
          {$this->noteTagsTable}.tag_id = :tagId
          AND
          (
            {$this->notesTable}.title LIKE :keyword
            OR
            {$this->notesTable}.content LIKE :keyword
          )
        ORDER BY {$this->notesTable}.is_pinned DESC, {$this->notesTable}.{$sortBy} {$orderDirection}"
    );

    $stmt->execute([
      ':tagId' => $tagId,
      ':keyword' => '%' . $keyword . '%',
    ]);

    return $stmt->fetchAll();
  }
}
