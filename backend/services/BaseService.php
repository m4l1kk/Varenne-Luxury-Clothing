<?php
// Shared business logic for every service. A child service passes its DAO and the entity
// name used in messages, and overrides validate() with the rules for its own table.
// Services never write SQL: all database work goes through the DAO.

require_once __DIR__ . '/ApiException.php';

class BaseService
{
    protected BaseDao $dao;
    protected string $entity;

    public function __construct(BaseDao $dao, string $entity)
    {
        $this->dao = $dao;
        $this->entity = $entity;
    }

    public function getAll(): array
    {
        return $this->dao->getAll();
    }

    // Returns the row or throws 404, so routes never have to check for null.
    public function getById(int $id): array
    {
        $row = $this->dao->getById($id);
        if ($row === null) {
            throw new ApiException("{$this->entity} $id not found", 404);
        }
        return $row;
    }

    public function add(array $data): array
    {
        $this->rejectNestedValues($data);
        return $this->dao->add($this->validate($data, null));
    }

    // Partial update: only the fields that are sent are checked and changed.
    public function update(int $id, array $data): array
    {
        $current = $this->getById($id);
        $this->rejectNestedValues($data);
        $data = $this->validate($data, $current);
        if (empty($data)) {
            throw new ApiException('Nothing to update');
        }
        return $this->dao->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->getById($id);
        $this->dao->delete($id);
    }

    // Checks the input and returns only the fields that may be saved.
    // $current is null when creating, and the existing row when updating.
    protected function validate(array $data, ?array $current): array
    {
        return $data;
    }

    // ----- Helpers used by the child services -----

    // Every column holds one value, so {"phone": ["a", "b"]} is refused with 400 here
    // instead of failing later in PDO with a 500.
    protected function rejectNestedValues(array $data): void
    {
        foreach ($data as $field => $value) {
            if (is_array($value) || is_object($value)) {
                throw new ApiException("Field '$field' must be a single value, not a list or object");
            }
        }
    }

    // On create every field in $fields must be present; on update only the ones that are sent are checked.
    protected function requireText(array $data, array $fields, ?array $current, int $maxLength = 255): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                if ($current === null) {
                    throw new ApiException("Field '$field' is required");
                }
                continue;
            }
            if (!is_string($data[$field]) || trim($data[$field]) === '') {
                throw new ApiException("Field '$field' must be a non-empty text");
            }
            if (mb_strlen($data[$field]) > $maxLength) {
                throw new ApiException("Field '$field' can have at most $maxLength characters");
            }
        }
    }

    // Accepts 5 or "5", rejects 5.5, "abc" and numbers below $min.
    protected function toInt($value, string $field, int $min = 1): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < $min) {
            throw new ApiException("Field '$field' must be a whole number of at least $min");
        }
        return (int) $value;
    }

    // Checks that a foreign key points to an existing row (gives a clear 400 instead of a database error).
    protected function requireExisting(BaseDao $dao, $id, string $field, string $entity): int
    {
        $id = $this->toInt($id, $field);
        if ($dao->getById($id) === null) {
            throw new ApiException("$entity $id does not exist");
        }
        return $id;
    }
}
