<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class ReviewService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new ReviewDao(), 'Review');
    }

    // Reviews of one product plus the average rating, for the #product view.
    public function getByProductId(int $productId): array
    {
        $this->requireExisting(new ProductDao(), $productId, 'product_id', 'Product');
        return [
            'summary' => $this->dao->getRatingSummary($productId),
            'reviews' => $this->dao->getByProductId($productId),
        ];
    }

    protected function validate(array $data, ?array $current): array
    {
        $clean = array_intersect_key($data, array_flip(['user_id', 'product_id', 'rating', 'comment']));

        // Who wrote the review and for which product is fixed once it exists.
        if ($current !== null) {
            unset($clean['user_id'], $clean['product_id']);
        } else {
            $clean['user_id'] = $this->requireExisting(new UserDao(), $clean['user_id'] ?? null, 'user_id', 'User');
            $clean['product_id'] = $this->requireExisting(new ProductDao(), $clean['product_id'] ?? null, 'product_id', 'Product');
            foreach ($this->dao->getByProductId($clean['product_id']) as $review) {
                if ($review['user_id'] == $clean['user_id']) {
                    throw new ApiException('This user has already reviewed this product', 409);
                }
            }
        }

        if ($current === null || isset($clean['rating'])) {
            $rating = $this->toInt($clean['rating'] ?? null, 'rating');
            if ($rating > 5) {
                throw new ApiException('Rating must be between 1 and 5');
            }
            $clean['rating'] = $rating;
        }
        if (isset($clean['comment'])) {
            if (!is_string($clean['comment']) || mb_strlen($clean['comment']) > 2000) {
                throw new ApiException('Comment must be text of at most 2000 characters');
            }
            $clean['comment'] = trim($clean['comment']);
        }

        return $clean;
    }
}
