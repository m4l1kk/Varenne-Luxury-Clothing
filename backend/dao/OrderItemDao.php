<?php
require_once __DIR__ . '/BaseDao.php';

class OrderItemDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('order_items', ['order_id', 'variant_id', 'quantity', 'unit_price']);
    }

    // Lines of one order with product name, size and colour.
    public function getByOrderId(int $orderId): array
    {
        return $this->query(
            'SELECT oi.*, p.name, v.size, v.color
             FROM order_items oi
             JOIN product_variants v ON v.id = oi.variant_id
             JOIN products p ON p.id = v.product_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.id',
            ['order_id' => $orderId]
        );
    }
}
