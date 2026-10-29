<?php
require_once __DIR__ . '/BaseDao.php';

class CategoryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('categories', ['name', 'slug', 'description']);
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->queryOne('SELECT * FROM categories WHERE slug = :slug', ['slug' => $slug]);
    }

    // Categories with how many products each one has (for the dashboard).
    public function getAllWithProductCount(): array
    {
        return $this->query(
            'SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.id'
        );
    }
}
