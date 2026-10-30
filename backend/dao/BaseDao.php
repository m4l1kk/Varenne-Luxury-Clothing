<?php
// Reusable CRUD methods shared by every DAO. A child DAO only says which table it uses
// and which columns may be written; all SQL goes through prepared statements.

require_once __DIR__ . '/Database.php';

class BaseDao
{
    protected PDO $connection;
    protected string $table;

    // Columns that add() and update() may write. Column names cannot be bound as
    // parameters, so only names from this list are ever put into the SQL text.
    protected array $columns;

    public function __construct(string $table, array $columns)
    {
        $this->connection = Database::connect();
        $this->table = $table;
        $this->columns = $columns;
    }

    // READ: every row of the table.
    public function getAll(): array
    {
        return $this->query("SELECT * FROM {$this->table} ORDER BY id");
    }

    // READ: one row by primary key, or null if it does not exist.
    public function getById(int $id): ?array
    {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE id = :id", ['id' => $id]);
    }

    // CREATE: inserts a row and returns it with its new id.
    public function add(array $data): array
    {
        $data = $this->onlyAllowedColumns($data);

        $columns = implode(', ', array_keys($data));                                     // first_name, email
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));    // :first_name, :email

        $stmt = $this->connection->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        $stmt->execute($data);

        return $this->getById((int) $this->connection->lastInsertId());
    }

    // UPDATE: changes only the columns passed in $data and returns the updated row
    // (null if the id does not exist).
    public function update(int $id, array $data): ?array
    {
        $data = $this->onlyAllowedColumns($data);

        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));        // first_name = :first_name

        $stmt = $this->connection->prepare("UPDATE {$this->table} SET $set WHERE id = :id");
        $stmt->execute($data + ['id' => $id]);

        return $this->getById($id);
    }

    // DELETE: removes a row; returns true if a row was deleted.
    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    // Helpers for child DAOs: run a prepared SELECT and return all rows / the first row.
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    // Drops keys that are not real, writable columns (e.g. "id" or a typo) and
    // fails if nothing is left to write.
    private function onlyAllowedColumns(array $data): array
    {
        $data = array_intersect_key($data, array_flip($this->columns));

        if (empty($data)) {
            throw new InvalidArgumentException("No valid columns given for table {$this->table}");
        }

        // execute() sends every value as a string, and false becomes '' which MySQL
        // rejects for TINYINT(1) columns such as is_active, so booleans are sent as 1 / 0.
        return array_map(fn($value) => is_bool($value) ? (int) $value : $value, $data);
    }
}
