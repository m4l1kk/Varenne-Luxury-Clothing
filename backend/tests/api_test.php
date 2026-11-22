<?php
// Calls the real REST API over HTTP and checks the status codes and answers.
// Apache and MySQL must be running. Run from the project root:
//   php backend/tests/api_test.php
// It creates its own test rows and deletes them again at the end; the sample data is only read.
// Another base URL can be passed as the first argument.

$base = $argv[1] ?? 'http://localhost/MalikIslamovic/Foundations-of-Frontend/backend';

$passed = 0;
$failed = 0;

function check(string $label, bool $ok): void
{
    global $passed, $failed;
    $ok ? $passed++ : $failed++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
}

// Sends a request and returns [status code, decoded JSON body].
function api(string $method, string $path, ?array $body = null): array
{
    global $base;
    $curl = curl_init($base . $path);
    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    if ($body !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return [$status, json_decode((string) $response, true)];
}

[$status] = api('GET', '/');
if ($status !== 200) {
    exit("The API is not reachable at $base (status $status). Start Apache and MySQL first." . PHP_EOL);
}

// ---------- Categories ----------
[$s, $category] = api('POST', '/categories', ['name' => 'API Test Category']);
check('POST /categories creates a category (201) with a slug made from the name', $s === 201 && $category['slug'] === 'api-test-category');
[$s] = api('POST', '/categories', ['name' => 'API Test Category']);
check('POST /categories with the same name is refused (409)', $s === 409);
[$s] = api('POST', '/categories', ['description' => 'no name']);
check('POST /categories without a name is refused (400)', $s === 400);
[$s, $body] = api('PUT', "/categories/{$category['id']}", ['description' => 'Changed']);
check('PUT /categories/{id} changes the description', $s === 200 && $body['description'] === 'Changed');
[$s, $list] = api('GET', '/categories');
check('GET /categories includes product_count', $s === 200 && array_key_exists('product_count', $list[0]));

// ---------- Products ----------
[$s, $product] = api('POST', '/products', ['category_id' => $category['id'], 'name' => 'API Test Coat', 'price' => 150]);
check('POST /products creates a product (201)', $s === 201 && $product['name'] === 'API Test Coat');
[$s] = api('POST', '/products', ['category_id' => $category['id'], 'name' => 'Bad price', 'price' => -5]);
check('POST /products with a negative price is refused (400)', $s === 400);
[$s] = api('POST', '/products', ['category_id' => 99999, 'name' => 'No category', 'price' => 10]);
check('POST /products with a category that does not exist is refused (400)', $s === 400);
[$s, $body] = api('PUT', "/products/{$product['id']}", ['price' => '199.99']);
check('PUT /products/{id} changes the price', $s === 200 && $body['price'] == 199.99);
[$s, $body] = api('GET', '/products?search=API%20Test');
check('GET /products?search= finds the product', $s === 200 && in_array($product['id'], array_column($body, 'id')));
[$s, $body] = api('GET', "/products?category_id={$category['id']}");
check('GET /products?category_id= filters by category', $s === 200 && count($body) === 1);
[$s] = api('GET', '/products/99999');
check('GET /products/{id} that does not exist returns 404', $s === 404);
[$s] = api('DELETE', "/categories/{$category['id']}");
check('DELETE /categories/{id} is refused while it has products (409)', $s === 409);

// ---------- Variants ----------
$sku = 'API-TEST-' . time();
[$s, $variant] = api('POST', '/variants', ['product_id' => $product['id'], 'size' => 'M', 'color' => 'Black', 'sku' => $sku, 'stock' => 2]);
check('POST /variants creates a variant (201)', $s === 201 && $variant['stock'] == 2);
[$s] = api('POST', '/variants', ['product_id' => $product['id'], 'size' => 'L', 'color' => 'Black', 'sku' => $sku]);
check('POST /variants with a used SKU is refused (409)', $s === 409);
[$s] = api('PUT', "/variants/{$variant['id']}", ['stock' => -1]);
check('PUT /variants/{id} with negative stock is refused (400)', $s === 400);
[$s, $body] = api('GET', "/products/{$product['id']}/variants");
check('GET /products/{id}/variants lists the variant', $s === 200 && count($body) === 1);

// ---------- Users ----------
$email = 'api-test-' . time() . '@example.com';
[$s, $user] = api('POST', '/users', ['first_name' => 'Api', 'last_name' => 'Tester', 'email' => $email, 'password' => 'Secret123!']);
check('POST /users creates a user (201)', $s === 201 && $user['email'] === $email);
check('POST /users never returns a password or hash', !isset($user['password']) && !isset($user['password_hash']));
[$s] = api('POST', '/users', ['first_name' => 'Api', 'last_name' => 'Tester', 'email' => $email, 'password' => 'Secret123!']);
check('POST /users with a used email is refused (409)', $s === 409);
[$s] = api('POST', '/users', ['first_name' => 'Api', 'last_name' => 'Tester', 'email' => 'not-an-email', 'password' => 'Secret123!']);
check('POST /users with an invalid email is refused (400)', $s === 400);
[$s] = api('POST', '/users', ['first_name' => 'Api', 'last_name' => 'Tester', 'email' => 'x' . $email, 'password' => 'short']);
check('POST /users with a short password is refused (400)', $s === 400);
[$s] = api('PUT', "/users/{$user['id']}", ['phone' => ['+387', '61']]);
check('PUT /users/{id} with a list instead of a value is refused (400, not 500)', $s === 400);
[$s, $body] = api('PUT', "/users/{$user['id']}", ['phone' => '+387 61 000 999']);
check('PUT /users/{id} changes the phone', $s === 200 && $body['phone'] === '+387 61 000 999');
[$s, $body] = api('GET', '/users?role=admin');
check('GET /users?role=admin lists only admins', $s === 200 && count(array_unique(array_column($body, 'role'))) === 1);

// ---------- Addresses ----------
$addressData = ['user_id' => $user['id'], 'street' => 'Test 1', 'city' => 'Sarajevo', 'postal_code' => '71000', 'country' => 'BiH', 'is_default' => true];
[$s, $address1] = api('POST', '/addresses', $addressData);
[$s2, $address2] = api('POST', '/addresses', ['street' => 'Test 2'] + $addressData);
check('POST /addresses creates two addresses (201)', $s === 201 && $s2 === 201);
[$s, $body] = api('GET', "/users/{$user['id']}/addresses");
check('Only one address stays default', $s === 200 && array_sum(array_column($body, 'is_default')) === 1 && $body[0]['id'] === $address2['id']);

// ---------- Cart ----------
[$s] = api('POST', '/cart-items', ['user_id' => $user['id'], 'variant_id' => $variant['id'], 'quantity' => 3]);
check('POST /cart-items with more than the stock is refused (409)', $s === 409);
[$s, $line] = api('POST', '/cart-items', ['user_id' => $user['id'], 'variant_id' => $variant['id'], 'quantity' => 1]);
check('POST /cart-items adds to the bag (201)', $s === 201 && $line['quantity'] == 1);
[$s, $line] = api('POST', '/cart-items', ['user_id' => $user['id'], 'variant_id' => $variant['id'], 'quantity' => 1]);
check('Adding the same variant again raises the quantity to 2', $s === 201 && $line['quantity'] == 2);
[$s, $cart] = api('GET', "/users/{$user['id']}/cart");
check('GET /users/{id}/cart returns the total (2 x 199.99)', $s === 200 && abs($cart['total'] - 399.98) < 0.001);
[$s] = api('PUT', "/cart-items/{$line['id']}", ['quantity' => 0]);
check('PUT /cart-items/{id} with quantity 0 is refused (400)', $s === 400);

// ---------- Orders (checkout) ----------
[$s] = api('POST', '/orders', ['user_id' => $user['id'], 'address_id' => 1]);
check("POST /orders with another customer's address is refused (400)", $s === 400);
[$s, $order] = api('POST', '/orders', ['user_id' => $user['id'], 'address_id' => $address2['id']]);
check('POST /orders turns the bag into an order (201)', $s === 201 && $order['status'] === 'pending');
check('The order total is taken from the database prices', abs($order['total'] - 399.98) < 0.001 && count($order['items']) === 1);
[$s, $body] = api('GET', "/variants/{$variant['id']}");
check('Checkout lowered the stock from 2 to 0', $body['stock'] == 0);
[$s, $cart] = api('GET', "/users/{$user['id']}/cart");
check('Checkout emptied the bag', count($cart['items']) === 0);
[$s] = api('POST', '/orders', ['user_id' => $user['id']]);
check('POST /orders with an empty bag is refused (400)', $s === 400);
[$s, $body] = api('PUT', "/orders/{$order['id']}", ['status' => 'shipped']);
check('PUT /orders/{id} changes the status', $s === 200 && $body['status'] === 'shipped');
[$s] = api('PUT', "/orders/{$order['id']}", ['status' => 'lost']);
check('PUT /orders/{id} with an unknown status is refused (400)', $s === 400);
[$s, $body] = api('GET', '/orders?status=shipped');
check('GET /orders?status=shipped includes the order', $s === 200 && in_array($order['id'], array_column($body, 'id')));
[$s] = api('DELETE', "/products/{$product['id']}");
check('DELETE /products/{id} is refused after it was ordered (409)', $s === 409);
[$s] = api('DELETE', "/users/{$user['id']}");
check('DELETE /users/{id} is refused while the user has orders (409)', $s === 409);

// ---------- Order items ----------
[$s, $orderItem] = api('POST', '/order-items', ['order_id' => $order['id'], 'variant_id' => $variant['id'], 'quantity' => 1]);
check('POST /order-items adds a line with the product price (201)', $s === 201 && $orderItem['unit_price'] == 199.99);
[$s, $body] = api('GET', "/orders/{$order['id']}");
check('Adding a line recalculates the order total (3 x 199.99)', abs($body['total'] - 599.97) < 0.001);
[$s, $body] = api('PUT', "/order-items/{$orderItem['id']}", ['quantity' => 2, 'unit_price' => 100]);
check('PUT /order-items/{id} changes quantity and price', $s === 200 && $body['quantity'] == 2);
[$s, $body] = api('GET', "/orders/{$order['id']}");
check('Changing a line recalculates the total (399.98 + 200)', abs($body['total'] - 599.98) < 0.001);
[$s] = api('POST', '/order-items', ['order_id' => 99999, 'variant_id' => $variant['id']]);
check('POST /order-items for an order that does not exist is refused (400)', $s === 400);
[$s, $body] = api('GET', "/orders/{$order['id']}/items");
check('GET /orders/{id}/items lists both lines', $s === 200 && count($body) === 2);
[$s, $body] = api('GET', '/order-items');
check('GET /order-items lists every line', $s === 200 && in_array($orderItem['id'], array_column($body, 'id')));
[$s] = api('DELETE', "/order-items/{$orderItem['id']}");
[$s2, $body] = api('GET', "/orders/{$order['id']}");
check('DELETE /order-items/{id} removes the line and the total goes back', $s === 200 && abs($body['total'] - 399.98) < 0.001);

// ---------- Wishlist ----------
[$s, $wish] = api('POST', '/wishlist-items', ['user_id' => $user['id'], 'product_id' => $product['id']]);
check('POST /wishlist-items saves a product (201)', $s === 201);
[$s] = api('POST', '/wishlist-items', ['user_id' => $user['id'], 'product_id' => $product['id']]);
check('Saving the same product twice is refused (409)', $s === 409);
[$s, $body] = api('PUT', "/wishlist-items/{$wish['id']}", ['product_id' => 1]);
check('PUT /wishlist-items/{id} swaps the product', $s === 200 && $body['product_id'] == 1);
[$s, $body] = api('GET', "/wishlist-items/{$wish['id']}");
check('GET /wishlist-items/{id} returns it', $s === 200 && $body['user_id'] == $user['id']);
[$s] = api('DELETE', "/wishlist-items/{$wish['id']}");
check('DELETE /wishlist-items/{id} removes it', $s === 200);

// ---------- Reviews ----------
[$s] = api('POST', '/reviews', ['user_id' => $user['id'], 'product_id' => $product['id'], 'rating' => 6]);
check('POST /reviews with rating 6 is refused (400)', $s === 400);
[$s, $review] = api('POST', '/reviews', ['user_id' => $user['id'], 'product_id' => $product['id'], 'rating' => 4, 'comment' => 'Nice']);
check('POST /reviews creates a review (201)', $s === 201 && $review['rating'] == 4);
[$s] = api('POST', '/reviews', ['user_id' => $user['id'], 'product_id' => $product['id'], 'rating' => 5]);
check('A second review of the same product is refused (409)', $s === 409);
[$s, $body] = api('PUT', "/reviews/{$review['id']}", ['rating' => 5]);
check('PUT /reviews/{id} changes the rating', $s === 200 && $body['rating'] == 5);
[$s, $body] = api('GET', "/products/{$product['id']}/reviews");
check('GET /products/{id}/reviews returns the average rating', $s === 200 && $body['summary']['average_rating'] == 5);

// ---------- List endpoints ----------
foreach (['/users', '/addresses', '/categories', '/products', '/variants', '/cart-items', '/wishlist-items', '/orders', '/order-items', '/reviews'] as $path) {
    [$s, $body] = api('GET', $path);
    check("GET $path returns a list (200)", $s === 200 && is_array($body));
}

// ---------- Errors ----------
[$s, $body] = api('GET', '/does-not-exist');
check('An unknown route returns a JSON 404', $s === 404 && isset($body['error']));

// ---------- Clean up (also tests DELETE) ----------
[$s] = api('DELETE', "/reviews/{$review['id']}");
check('DELETE /reviews/{id} (200)', $s === 200);
[$s] = api('DELETE', "/orders/{$order['id']}");
check('DELETE /orders/{id} (200)', $s === 200);
[$s] = api('GET', "/orders/{$order['id']}");
check('The deleted order returns 404', $s === 404);
api('DELETE', "/addresses/{$address1['id']}");
[$s] = api('DELETE', "/addresses/{$address2['id']}");
check('DELETE /addresses/{id} (200)', $s === 200);
[$s] = api('DELETE', "/variants/{$variant['id']}");
check('DELETE /variants/{id} (200)', $s === 200);
[$s] = api('DELETE', "/products/{$product['id']}");
check('DELETE /products/{id} works once no order uses it (200)', $s === 200);
[$s] = api('DELETE', "/categories/{$category['id']}");
check('DELETE /categories/{id} works once it is empty (200)', $s === 200);
[$s] = api('DELETE', "/users/{$user['id']}");
check('DELETE /users/{id} works once the user has no orders (200)', $s === 200);
[$s] = api('DELETE', "/users/{$user['id']}");
check('Deleting it again returns 404', $s === 404);

echo PHP_EOL . "$passed passed, $failed failed" . PHP_EOL;
