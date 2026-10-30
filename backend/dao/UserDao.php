<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    // Every column except password_hash, so the hash is never sent back by getAll/getById.
    private const PUBLIC_COLUMNS = 'id, first_name, last_name, email, phone, role, created_at';

    public function __construct()
    {
        parent::__construct('users', ['first_name', 'last_name', 'email', 'password_hash', 'phone', 'role']);
    }

    public function getAll(): array
    {
        return $this->query('SELECT ' . self::PUBLIC_COLUMNS . ' FROM users ORDER BY id');
    }

    public function getById(int $id): ?array
    {
        return $this->queryOne('SELECT ' . self::PUBLIC_COLUMNS . ' FROM users WHERE id = :id', ['id' => $id]);
    }

    // Includes password_hash on purpose: login (Milestone 4) needs it for password_verify().
    public function getByEmail(string $email): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE email = :email', ['email' => $email]);
    }

    public function getByRole(string $role): array
    {
        return $this->query('SELECT ' . self::PUBLIC_COLUMNS . ' FROM users WHERE role = :role ORDER BY id', ['role' => $role]);
    }
}
