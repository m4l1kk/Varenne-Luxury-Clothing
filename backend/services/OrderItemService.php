<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/ProductVariantDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';

// Order lines are normally created by checkout (OrderService::add). These methods let an admin
// correct an order afterwards; every change recalculates the order total, so it always equals
// the sum of its lines.
class OrderItemService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new OrderItemDao(), 'Order item');
    }

    public function getByOrderId(int $orderId): array
    {
        $this->requireExisting(new OrderDao(), $orderId, 'order_id', 'Order');
        return $this->dao->getByOrderId($orderId);
    }

    public function add(array $data): array
    {
        $item = parent::add($data);
        $this->recalculateTotal((int) $item['order_id']);
        return $item;
    }

    public function update(int $id, array $data): array
    {
        $item = parent::update($id, $data);
        $this->recalculateTotal((int) $item['order_id']);
        return $item;
    }

    public function delete(int $id): void
    {
        $item = $this->getById($id);
        $this->dao->delete($id);
        $this->recalculateTotal((int) $item['order_id']);
    }

    protected function validate(array $data, ?array $current): array
    {
        $clean = [];

        // The order a line belongs to never changes.
        if ($current === null) {
            $clean['order_id'] = $this->requireExisting(new OrderDao(), $data['order_id'] ?? null, 'order_id', 'Order');
        }
        if ($current === null || isset($data['variant_id'])) {
            $clean['variant_id'] = $this->requireExisting(new ProductVariantDao(), $data['variant_id'] ?? null, 'variant_id', 'Variant');
        }
        if ($current === null || isset($data['quantity'])) {
            $clean['quantity'] = $this->toInt($data['quantity'] ?? 1, 'quantity');
        }

        // The unit price is the product's current price unless the admin sets one (e.g. a discount).
        if (isset($data['unit_price'])) {
            if (!is_numeric($data['unit_price']) || $data['unit_price'] <= 0) {
                throw new ApiException('Unit price must be a number greater than 0');
            }
            $clean['unit_price'] = round((float) $data['unit_price'], 2);
        } elseif ($current === null) {
            $variant = (new ProductVariantDao())->getById($clean['variant_id']);
            $clean['unit_price'] = (float) (new ProductDao())->getById((int) $variant['product_id'])['price'];
        }

        return $clean;
    }

    private function recalculateTotal(int $orderId): void
    {
        $total = 0;
        foreach ($this->dao->getByOrderId($orderId) as $line) {
            $total += $line['quantity'] * $line['unit_price'];
        }
        (new OrderDao())->update($orderId, ['total' => round($total, 2)]);
    }
}
