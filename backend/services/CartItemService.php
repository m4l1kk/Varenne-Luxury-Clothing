<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/CartItemDao.php';
require_once __DIR__ . '/../dao/ProductVariantDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class CartItemService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new CartItemDao(), 'Cart item');
    }

    // The bag of one user, with the line totals and the bag total worked out here.
    public function getByUserId(int $userId): array
    {
        $this->requireExisting(new UserDao(), $userId, 'user_id', 'User');
        $items = $this->dao->getByUserId($userId);
        $total = 0;
        foreach ($items as &$item) {
            $item['line_total'] = round($item['price'] * $item['quantity'], 2);
            $total += $item['line_total'];
        }
        return ['items' => $items, 'total' => round($total, 2)];
    }

    // Adding a size/colour that is already in the bag raises its quantity instead of failing.
    public function add(array $data): array
    {
        $data = $this->validate($data, null);
        $existing = $this->dao->getByUserAndVariant($data['user_id'], $data['variant_id']);
        if ($existing !== null) {
            $quantity = $existing['quantity'] + $data['quantity'];
            $this->checkStock($data['variant_id'], $quantity);
            return $this->dao->update($existing['id'], ['quantity' => $quantity]);
        }
        return $this->dao->add($data);
    }

    // Only the quantity of a bag line can change; to change size, remove it and add the other variant.
    public function update(int $id, array $data): array
    {
        $current = $this->getById($id);
        if (!isset($data['quantity'])) {
            throw new ApiException("Field 'quantity' is required");
        }
        $quantity = $this->toInt($data['quantity'], 'quantity');
        $this->checkStock($current['variant_id'], $quantity);
        return $this->dao->update($id, ['quantity' => $quantity]);
    }

    public function clear(int $userId): int
    {
        $this->requireExisting(new UserDao(), $userId, 'user_id', 'User');
        return $this->dao->clearForUser($userId);
    }

    protected function validate(array $data, ?array $current): array
    {
        $clean = [
            'user_id'    => $this->requireExisting(new UserDao(), $data['user_id'] ?? null, 'user_id', 'User'),
            'variant_id' => $this->requireExisting(new ProductVariantDao(), $data['variant_id'] ?? null, 'variant_id', 'Variant'),
            'quantity'   => $this->toInt($data['quantity'] ?? 1, 'quantity'),
        ];
        $this->checkStock($clean['variant_id'], $clean['quantity']);
        return $clean;
    }

    private function checkStock(int $variantId, int $quantity): void
    {
        $variant = (new ProductVariantDao())->getById($variantId);
        if ($quantity > $variant['stock']) {
            throw new ApiException("Only {$variant['stock']} left in stock for this size and colour", 409);
        }
    }
}
