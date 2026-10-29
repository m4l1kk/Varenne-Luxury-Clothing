<?php
require_once __DIR__ . '/BaseDao.php';

class ReviewDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('reviews', ['user_id', 'product_id', 'rating', 'comment']);
    }

    // Reviews on the #product page with the reviewer's first name.
    public function getByProductId(int $productId): array
    {
        return $this->query(
            'SELECT r.*, u.first_name
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id = :product_id
             ORDER BY r.created_at DESC',
            ['product_id' => $productId]
        );
    }

    // Average rating and number of reviews for one product.
    public function getRatingSummary(int $productId): array
    {
        return $this->queryOne(
            'SELECT COUNT(*) AS review_count, ROUND(AVG(rating), 1) AS average_rating
             FROM reviews WHERE product_id = :product_id',
            ['product_id' => $productId]
        );
    }
}
