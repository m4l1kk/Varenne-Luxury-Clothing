<?php
// Checks Create, Read, Update and Delete for every DAO against the real database.
// Run from the project root:   php backend/tests/dao_test.php
// or open it in the browser:   http://localhost/<path-to-repo>/backend/tests/dao_test.php
// Everything runs inside a transaction that is rolled back at the end, so the sample data is not changed.

foreach (glob(__DIR__ . '/../dao/*Dao.php') as $file) {
    require_once $file;
}

header('Content-Type: text/plain; charset=utf-8');

$passed = 0;
$failed = 0;

function check(string $label, bool $ok): void
{
    global $passed, $failed;
    $ok ? $passed++ : $failed++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
}

// Runs add -> getById -> update -> getAll -> delete -> getById on one DAO.
function crudTest(string $name, BaseDao $dao, array $newRow, array $changes): void
{
    $created = $dao->add($newRow);
    check("$name: add() returns the new row with an id", isset($created['id']));

    $read = $dao->getById($created['id']);
    check("$name: getById() finds it", $read !== null);

    $updated = $dao->update($created['id'], $changes);
    $key = array_key_first($changes);
    check("$name: update() changes $key", $updated[$key] == $changes[$key]);

    $all = $dao->getAll();
    check("$name: getAll() includes it", in_array($created['id'], array_column($all, 'id')));

    check("$name: delete() removes it", $dao->delete($created['id']) && $dao->getById($created['id']) === null);
}

$db = Database::connect();
$db->beginTransaction();

try {
    // Parent rows are created first so the child tests have valid foreign keys.
    $userDao = new UserDao();
    $user = $userDao->add([
        'first_name' => 'Test', 'last_name' => 'User', 'email' => 'dao-test@example.com',
        'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
    ]);
    check('users: password_hash is not returned', !array_key_exists('password_hash', $user));
    check('users: getByEmail() returns the hash for login', isset($userDao->getByEmail('dao-test@example.com')['password_hash']));

    $categoryDao = new CategoryDao();
    $category = $categoryDao->add(['name' => 'Test Category', 'slug' => 'test-category']);

    $productDao = new ProductDao();
    $product = $productDao->add(['category_id' => $category['id'], 'name' => 'Test Product', 'price' => 100]);

    $variantDao = new ProductVariantDao();
    $variant = $variantDao->add(['product_id' => $product['id'], 'size' => 'M', 'color' => 'Black', 'sku' => 'TEST-SKU-1', 'stock' => 5]);

    $orderDao = new OrderDao();
    $order = $orderDao->add(['user_id' => $user['id'], 'total' => 100]);

    crudTest('users', $userDao,
        ['first_name' => 'Ana', 'last_name' => 'Test', 'email' => 'ana-test@example.com', 'password_hash' => 'x'],
        ['phone' => '+387 61 999 999']);
    crudTest('addresses', new AddressDao(),
        ['user_id' => $user['id'], 'street' => 'Test 1', 'city' => 'Sarajevo', 'postal_code' => '71000', 'country' => 'BiH'],
        ['city' => 'Mostar']);
    crudTest('categories', $categoryDao,
        ['name' => 'Shoes', 'slug' => 'shoes'],
        ['description' => 'Updated']);
    crudTest('products', $productDao,
        ['category_id' => $category['id'], 'name' => 'Linen Shirt', 'price' => 250],
        ['price' => 199.99]);
    crudTest('product_variants', $variantDao,
        ['product_id' => $product['id'], 'size' => 'L', 'color' => 'White', 'sku' => 'TEST-SKU-2', 'stock' => 1],
        ['stock' => 7]);
    crudTest('cart_items', new CartItemDao(),
        ['user_id' => $user['id'], 'variant_id' => $variant['id'], 'quantity' => 1],
        ['quantity' => 3]);
    crudTest('wishlist_items', new WishlistItemDao(),
        ['user_id' => $user['id'], 'product_id' => $product['id']],
        ['product_id' => 1]);
    crudTest('orders', $orderDao,
        ['user_id' => $user['id'], 'total' => 500],
        ['status' => 'shipped']);
    crudTest('order_items', new OrderItemDao(),
        ['order_id' => $order['id'], 'variant_id' => $variant['id'], 'quantity' => 2, 'unit_price' => 100],
        ['quantity' => 4]);
    crudTest('reviews', new ReviewDao(),
        ['user_id' => $user['id'], 'product_id' => $product['id'], 'rating' => 5, 'comment' => 'Great'],
        ['rating' => 3]);

    // A few entity-specific methods on the sample data.
    check('products: getAllWithCategory() joins the category name', ($productDao->getAllWithCategory()[0]['category_name'] ?? null) === 'Women');
    check('cart_items: getByUserId() returns Amina\'s bag', count((new CartItemDao())->getByUserId(2)) === 2);
    check('reviews: getRatingSummary() for product 2', (new ReviewDao())->getRatingSummary(2)['review_count'] == 1);
    check('product_variants: decreaseStock() refuses more than in stock', $variantDao->decreaseStock($variant['id'], 99) === false);
    check('product_variants: decreaseStock() refuses a negative quantity', $variantDao->decreaseStock($variant['id'], -5) === false);
    check('product_variants: decreaseStock() lowers the stock', $variantDao->decreaseStock($variant['id'], 2) && $variantDao->getById($variant['id'])['stock'] == 3);
    check('BaseDao: PHP booleans are saved as 1 / 0', $productDao->update($product['id'], ['is_active' => false])['is_active'] == 0);

    // Unknown columns are ignored, so they never reach the SQL text.
    try {
        $categoryDao->add(['name; DROP TABLE users' => 'x']);
        check('BaseDao: rejects data with no valid columns', false);
    } catch (InvalidArgumentException $e) {
        check('BaseDao: rejects data with no valid columns', true);
    }
} catch (Throwable $e) {
    check('Unexpected error: ' . $e->getMessage(), false);
} finally {
    $db->rollBack();
}

echo PHP_EOL . "$passed passed, $failed failed" . PHP_EOL;
