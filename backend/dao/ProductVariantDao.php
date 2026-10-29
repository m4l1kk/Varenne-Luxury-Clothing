<?php
require_once __DIR__ . '/BaseDao.php';

class ProductVariantDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('product_variants', ['product_id', 'size', 'color', 'sku', 'stock']);
    }

    public function getByProductId(int $productId): array
    {
        return $this->query(
            'SELECT * FROM product_variants WHERE product_id = :product_id ORDER BY id',
            ['product_id' => $productId]
        );
    }

    public function getBySku(string $sku): ?array
    {
        return $this->queryOne('SELECT * FROM product_variants WHERE sku = :sku', ['sku' => $sku]);
    }

    // Lowers stock only if enough is left; returns false if there was not enough stock.
    public function decreaseStock(int $id, int $quantity): bool
    {
        // A negative quantity would turn "stock - quantity" into an increase.
        if ($quantity <= 0) {
            return false;
        }

        $stmt = $this->connection->prepare(
            'UPDATE product_variants SET stock = stock - :quantity WHERE id = :id AND stock >= :min_stock'
        );
        $stmt->execute(['quantity' => $quantity, 'id' => $id, 'min_stock' => $quantity]);

        return $stmt->rowCount() > 0;
    }
}
