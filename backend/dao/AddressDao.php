<?php
require_once __DIR__ . '/BaseDao.php';

class AddressDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('addresses', ['user_id', 'street', 'city', 'postal_code', 'country', 'is_default']);
    }

    // The default address comes first.
    public function getByUserId(int $userId): array
    {
        return $this->query(
            'SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, id',
            ['user_id' => $userId]
        );
    }

    // Used before marking another address as default, so a user has only one.
    public function clearDefault(int $userId): void
    {
        $stmt = $this->connection->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }
}
