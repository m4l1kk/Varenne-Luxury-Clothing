<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ProductVariantDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';

class ProductVariantService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new ProductVariantDao(), 'Variant');
    }

    public function getByProductId(int $productId): array
    {
        $this->requireExisting(new ProductDao(), $productId, 'product_id', 'Product');
        return $this->dao->getByProductId($productId);
    }

    public function delete(int $id): void
    {
        $this->getById($id);
        try {
            $this->dao->delete($id);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new ApiException('This variant is part of existing orders. Set its stock to 0 instead.', 409);
            }
            throw $e;
        }
    }

    protected function validate(array $data, ?array $current): array
    {
        $this->requireText($data, ['size'], $current, 20);
        $this->requireText($data, ['color'], $current, 30);
        $this->requireText($data, ['sku'], $current, 40);

        $clean = array_intersect_key($data, array_flip(['product_id', 'size', 'color', 'sku', 'stock']));

        if ($current === null || isset($clean['product_id'])) {
            $clean['product_id'] = $this->requireExisting(new ProductDao(), $clean['product_id'] ?? null, 'product_id', 'Product');
        }
        if (isset($clean['stock'])) {
            $clean['stock'] = $this->toInt($clean['stock'], 'stock', 0);
        }
        if (isset($clean['sku'])) {
            $clean['sku'] = strtoupper(trim($clean['sku']));
            $owner = $this->dao->getBySku($clean['sku']);
            if ($owner !== null && ($current === null || $owner['id'] != $current['id'])) {
                throw new ApiException('A variant with this SKU already exists', 409);
            }
        }

        return $clean;
    }
}
