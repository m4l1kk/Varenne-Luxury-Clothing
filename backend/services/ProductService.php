<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/CategoryDao.php';

class ProductService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new ProductDao(), 'Product');
    }

    // GET /products, optionally filtered: ?category_id=2 or ?search=silk (only active products then).
    public function getFiltered(?string $categoryId, ?string $search): array
    {
        if ($search !== null && trim($search) !== '') {
            return $this->dao->searchByName(trim($search));
        }
        if ($categoryId !== null && $categoryId !== '') {
            return $this->dao->getByCategoryId($this->requireExisting(new CategoryDao(), $categoryId, 'category_id', 'Category'));
        }
        return $this->dao->getAllWithCategory();
    }

    // A product that was ordered cannot be deleted (order_items keep the history),
    // so the admin is told to hide it instead.
    public function delete(int $id): void
    {
        $this->getById($id);
        try {
            $this->dao->delete($id);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new ApiException('This product is part of existing orders. Hide it with is_active = false instead.', 409);
            }
            throw $e;
        }
    }

    protected function validate(array $data, ?array $current): array
    {
        $this->requireText($data, ['name'], $current, 120);

        $clean = array_intersect_key($data, array_flip(['category_id', 'name', 'description', 'price', 'image_url', 'is_active']));

        if ($current === null || isset($clean['category_id'])) {
            $clean['category_id'] = $this->requireExisting(new CategoryDao(), $clean['category_id'] ?? null, 'category_id', 'Category');
        }
        if ($current === null && !isset($clean['price'])) {
            throw new ApiException("Field 'price' is required");
        }
        if (isset($clean['price'])) {
            if (!is_numeric($clean['price']) || $clean['price'] <= 0 || $clean['price'] > 99999999) {
                throw new ApiException('Price must be a number greater than 0');
            }
            $clean['price'] = round((float) $clean['price'], 2);
        }
        if (isset($clean['is_active'])) {
            $clean['is_active'] = filter_var($clean['is_active'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($clean['name'])) {
            $clean['name'] = trim($clean['name']);
        }

        return $clean;
    }
}
