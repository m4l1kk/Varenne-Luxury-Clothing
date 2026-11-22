<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';

class CategoryService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new CategoryDao(), 'Category');
    }

    // The list includes product_count for the dashboard.
    public function getAll(): array
    {
        return $this->dao->getAllWithProductCount();
    }

    // The database refuses to delete a category that still has products (ON DELETE RESTRICT);
    // checking first gives the user a clear message instead of a database error.
    public function delete(int $id): void
    {
        $this->getById($id);
        if (count((new ProductDao())->getByCategoryId($id)) > 0) {
            throw new ApiException('This category still has products. Move or delete them first.', 409);
        }
        $this->dao->delete($id);
    }

    protected function validate(array $data, ?array $current): array
    {
        $this->requireText($data, ['name'], $current, 60);

        $clean = array_intersect_key($data, array_flip(['name', 'slug', 'description']));
        if (isset($clean['name'])) {
            $clean['name'] = trim($clean['name']);
        }

        // The slug is made from the name if it is not sent: "Evening Wear" -> "evening-wear".
        if (!isset($clean['slug']) && isset($clean['name'])) {
            $clean['slug'] = $clean['name'];
        }
        if (isset($clean['slug'])) {
            if (!is_string($clean['slug'])) {
                throw new ApiException('Slug must be text');
            }
            $clean['slug'] = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($clean['slug'])), '-');
            if ($clean['slug'] === '') {
                throw new ApiException('Slug must contain letters or numbers');
            }
            $owner = $this->dao->getBySlug($clean['slug']);
            if ($owner !== null && ($current === null || $owner['id'] != $current['id'])) {
                throw new ApiException('A category with this name or slug already exists', 409);
            }
        }

        return $clean;
    }
}
