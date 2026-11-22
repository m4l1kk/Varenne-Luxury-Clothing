<?php
// Backend entry point (FlightPHP). Every request to backend/ is sent here by .htaccess.
// Flow: routes/ (read the request) -> services/ (validation, business rules) -> dao/ (SQL).

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/utils/Logger.php';

foreach (glob(__DIR__ . '/services/*.php') as $file) {
    require_once $file;
}

// One shared instance of each service, used in the routes as Flight::productService() etc.
Flight::register('userService', 'UserService');
Flight::register('addressService', 'AddressService');
Flight::register('categoryService', 'CategoryService');
Flight::register('productService', 'ProductService');
Flight::register('variantService', 'ProductVariantService');
Flight::register('cartService', 'CartItemService');
Flight::register('wishlistService', 'WishlistItemService');
Flight::register('orderService', 'OrderService');
Flight::register('orderItemService', 'OrderItemService');
Flight::register('reviewService', 'ReviewService');
Flight::register('currencyService', 'CurrencyService');

foreach (glob(__DIR__ . '/routes/*Routes.php') as $file) {
    require_once $file;
}

// Error handling: every error becomes a JSON message. Expected errors (ApiException) keep their
// status code; anything else is written to logs/error.log and the client only gets a generic
// message, never a stack trace, SQL query or file path.
// jsonHalt() is used here instead of json(): in FlightPHP 3 a response written inside the error
// handler is not sent automatically, so json() alone produced an empty "200 OK".
Flight::map('error', function (Throwable $e) {
    Flight::response()->clearBody();

    if ($e instanceof ApiException) {
        Flight::jsonHalt(['error' => $e->getMessage()], $e->getStatus());
    }
    if ($e instanceof PDOException && $e->getCode() === '23000') {
        // Duplicate value or a row that other rows still depend on.
        Flight::jsonHalt(['error' => 'This change conflicts with existing data (duplicate value or linked records)'], 409);
    }

    Logger::error(sprintf('500 %s: %s (%s:%d)', get_class($e), $e->getMessage(), basename($e->getFile()), $e->getLine()));

    Flight::jsonHalt(['error' => 'Something went wrong on the server. Please try again later.'], 500);
});

Flight::map('notFound', function () {
    Flight::jsonHalt(['error' => 'Route not found'], 404);
});

/**
 * @OA\Get(
 *     path="/", tags={"status"}, summary="Check that the API is running",
 *     @OA\Response(response=200, description="API status")
 * )
 */
Flight::route('GET /', function () {
    Flight::json(['status' => 'ok', 'message' => 'Varenne API', 'docs' => 'public/v1/docs/']);
});

Flight::start();
