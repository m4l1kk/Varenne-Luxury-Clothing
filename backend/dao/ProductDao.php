<?php
require_once __DIR__ . '/BaseDao.php';

class ProductDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('products', ['category_id', 'name', 'description', 'price', 'image_url', 'is_active']);
    }

    // Products with their category name, for the shop grid.
    public function getAllWithCategory(): array
    {
        return $this->query(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             ORDER BY p.id'
        );
    }

    public function getByCategoryId(int $categoryId): array
    {
        return $this->query(
            'SELECT * FROM products WHERE category_id = :category_id AND is_active = 1 ORDER BY id',
            ['category_id' => $categoryId]
        );
    }

    // Search by name; the % wildcards are added to the value, not to the SQL text.
    public function searchByName(string $term): array
    {
        return $this->query(
            'SELECT * FROM products WHERE name LIKE :term AND is_active = 1 ORDER BY name',
            ['term' => '%' . $term . '%']
        );
    }
}
