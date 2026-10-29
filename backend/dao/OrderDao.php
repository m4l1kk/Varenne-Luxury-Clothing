<?php
require_once __DIR__ . '/BaseDao.php';

class OrderDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('orders', ['user_id', 'address_id', 'status', 'total', 'currency']);
    }

    // All orders with the customer's name, newest first (admin dashboard).
    public function getAllWithCustomer(): array
    {
        return $this->query(
            "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) AS customer
             FROM orders o
             JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC"
        );
    }

    // Order history on #profile.
    public function getByUserId(int $userId): array
    {
        return $this->query(
            'SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC',
            ['user_id' => $userId]
        );
    }

    public function getByStatus(string $status): array
    {
        return $this->query(
            'SELECT * FROM orders WHERE status = :status ORDER BY created_at DESC',
            ['status' => $status]
        );
    }
}
