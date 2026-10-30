<?php
require_once __DIR__ . '/BaseDao.php';

class WishlistItemDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('wishlist_items', ['user_id', 'product_id']);
    }

    // The user's saved pieces with product details, newest first.
    public function getByUserId(int $userId): array
    {
        return $this->query(
            'SELECT w.id, w.added_at, p.id AS product_id, p.name, p.price
             FROM wishlist_items w
             JOIN products p ON p.id = w.product_id
             WHERE w.user_id = :user_id
             ORDER BY w.added_at DESC',
            ['user_id' => $userId]
        );
    }
}
