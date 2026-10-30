<?php
require_once __DIR__ . '/BaseDao.php';

class CartItemDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('cart_items', ['user_id', 'variant_id', 'quantity']);
    }

    // The user's bag with product name, size, colour and price, for the #cart view.
    public function getByUserId(int $userId): array
    {
        return $this->query(
            'SELECT ci.id, ci.quantity, v.id AS variant_id, v.size, v.color, v.stock,
                    p.id AS product_id, p.name, p.price
             FROM cart_items ci
             JOIN product_variants v ON v.id = ci.variant_id
             JOIN products p ON p.id = v.product_id
             WHERE ci.user_id = :user_id
             ORDER BY ci.id',
            ['user_id' => $userId]
        );
    }

    // Finds the row for this size/colour in the user's bag (to raise its quantity instead of adding a duplicate).
    public function getByUserAndVariant(int $userId, int $variantId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM cart_items WHERE user_id = :user_id AND variant_id = :variant_id',
            ['user_id' => $userId, 'variant_id' => $variantId]
        );
    }

    // Empties the bag after checkout.
    public function clearForUser(int $userId): int
    {
        $stmt = $this->connection->prepare('DELETE FROM cart_items WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount();
    }
}
