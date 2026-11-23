<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/WishlistItemDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class WishlistItemService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new WishlistItemDao(), 'Wishlist item');
    }

    public function getByUserId(int $userId): array
    {
        $this->requireExisting(new UserDao(), $userId, 'user_id', 'User');
        return $this->dao->getByUserId($userId);
    }

    // On update only the product can change (swap one saved piece for another); the owner stays the same.
    protected function validate(array $data, ?array $current): array
    {
        $userId = $current === null
            ? $this->requireExisting(new UserDao(), $data['user_id'] ?? null, 'user_id', 'User')
            : (int) $current['user_id'];

        if ($current !== null && !isset($data['product_id'])) {
            return [];
        }
        $productId = $this->requireExisting(new ProductDao(), $data['product_id'] ?? null, 'product_id', 'Product');

        foreach ($this->dao->getByUserId($userId) as $item) {
            if ($item['product_id'] == $productId) {
                throw new ApiException('This product is already in the wishlist', 409);
            }
        }

        return $current === null ? ['user_id' => $userId, 'product_id' => $productId] : ['product_id' => $productId];
    }
}
