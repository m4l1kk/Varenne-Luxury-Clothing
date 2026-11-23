<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';
require_once __DIR__ . '/../dao/CartItemDao.php';
require_once __DIR__ . '/../dao/ProductVariantDao.php';
require_once __DIR__ . '/../dao/AddressDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class OrderService extends BaseService
{
    public const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    public function __construct()
    {
        parent::__construct(new OrderDao(), 'Order');
    }

    // GET /orders (admin list with customer names), optionally ?status=shipped.
    public function getFiltered(?string $status): array
    {
        if ($status !== null && $status !== '') {
            $this->checkStatus($status);
            return $this->dao->getByStatus($status);
        }
        return $this->dao->getAllWithCustomer();
    }

    // One order together with its lines.
    public function getWithItems(int $id): array
    {
        $order = $this->getById($id);
        $order['items'] = (new OrderItemDao())->getByOrderId($id);
        return $order;
    }

    public function getByUserId(int $userId): array
    {
        $this->requireExisting(new UserDao(), $userId, 'user_id', 'User');
        return $this->dao->getByUserId($userId);
    }

    // CHECKOUT: turns the user's bag into an order. The total and unit prices are taken from the
    // database, never from the client. Creating the order, its lines, lowering the stock and emptying
    // the bag happen in one transaction, so a failure in any step leaves nothing half-done.
    public function add(array $data): array
    {
        $userId = $this->requireExisting(new UserDao(), $data['user_id'] ?? null, 'user_id', 'User');
        $addressId = $this->checkAddress($data['address_id'] ?? null, $userId);

        $cartDao = new CartItemDao();
        $cart = $cartDao->getByUserId($userId);
        if (empty($cart)) {
            throw new ApiException('The bag is empty');
        }

        $total = 0;
        foreach ($cart as $line) {
            $total += $line['price'] * $line['quantity'];
        }

        $variantDao = new ProductVariantDao();
        $itemDao = new OrderItemDao();

        $this->dao->beginTransaction();
        try {
            $order = $this->dao->add([
                'user_id'    => $userId,
                'address_id' => $addressId,
                'status'     => 'pending',
                'total'      => round($total, 2),
                'currency'   => 'EUR',
            ]);
            foreach ($cart as $line) {
                if (!$variantDao->decreaseStock($line['variant_id'], $line['quantity'])) {
                    throw new ApiException("Not enough stock left for {$line['name']} ({$line['size']}, {$line['color']})", 409);
                }
                $itemDao->add([
                    'order_id'   => $order['id'],
                    'variant_id' => $line['variant_id'],
                    'quantity'   => $line['quantity'],
                    'unit_price' => $line['price'],
                ]);
            }
            $cartDao->clearForUser($userId);
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollBack();
            throw $e;
        }

        return $this->getWithItems($order['id']);
    }

    // Only the status and the delivery address of an order can change; the total and lines stay as ordered.
    protected function validate(array $data, ?array $current): array
    {
        $clean = [];
        if (isset($data['status'])) {
            $this->checkStatus($data['status']);
            $clean['status'] = $data['status'];
        }
        if (array_key_exists('address_id', $data)) {
            $clean['address_id'] = $this->checkAddress($data['address_id'], (int) $current['user_id']);
        }
        return $clean;
    }

    private function checkStatus($status): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new ApiException('Status must be one of: ' . implode(', ', self::STATUSES));
        }
    }

    // The address is optional, but if it is given it must belong to the same user.
    private function checkAddress($addressId, int $userId): ?int
    {
        if ($addressId === null || $addressId === '') {
            return null;
        }
        $address = (new AddressDao())->getById($this->toInt($addressId, 'address_id'));
        if ($address === null || (int) $address['user_id'] !== $userId) {
            throw new ApiException('This address does not belong to the customer');
        }
        return (int) $address['id'];
    }
}
