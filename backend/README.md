# Backend

PHP backend for Varenne. Milestone 2 added the MySQL database and the DAO layer; Milestone 3 added the
services, the FlightPHP REST routes, the OpenAPI/Swagger documentation and the Frankfurter currency converter.

| Folder | Purpose |
|---|---|
| `routes/` | FlightPHP routes with `@OA` comments (one file per entity, e.g. `ProductRoutes.php`) |
| `services/` | Business logic and validation (`BaseService.php`, `ProductService.php`, ...) |
| `public/v1/docs/` | Swagger UI (`index.html`) and `swagger.php`, which builds the OpenAPI JSON |
| `utils/` | `Logger.php`: one line per event in `logs/info.log` / `logs/error.log` |
| `dao/` | Database access with PDO: `Database.php` (connection), `BaseDao.php` (shared CRUD), one DAO per table |
| `database/` | `varenne.sql`: schema and sample data |
| `tests/` | `dao_test.php` (every DAO) and `api_test.php` (every endpoint over HTTP) |
| `middleware/` | Auth / role checks, request logging |
| `config/` | `config.example.php` is committed; copy it to `config.php` (ignored by Git) |
| `logs/` | Runtime logs, ignored by Git (only `.gitkeep` is tracked) |

## DAO layer

`Database::connect()` opens one PDO connection with the constants from `config/config.php`
(exceptions on errors, associative arrays, real prepared statements).

`BaseDao` gives every DAO the same five methods:

| Method | SQL | CRUD |
|---|---|---|
| `getAll()` | `SELECT * FROM table` | Read (GET) |
| `getById($id)` | `SELECT * ... WHERE id = :id` | Read (GET) |
| `add($data)` | `INSERT INTO table (...) VALUES (:...)` | Create (POST) |
| `update($id, $data)` | `UPDATE table SET col = :col ... WHERE id = :id` | Update (PUT/PATCH) |
| `delete($id)` | `DELETE FROM table WHERE id = :id` | Delete (DELETE) |

Each child DAO passes its table name and the list of columns it may write. `add()` and `update()` drop
any key that is not in that list, because column names cannot be bound as parameters.

| DAO | Table | Extra methods |
|---|---|---|
| `UserDao` | `users` | `getByEmail`, `getByRole` (never returns `password_hash` except `getByEmail`) |
| `AddressDao` | `addresses` | `getByUserId`, `clearDefault` |
| `CategoryDao` | `categories` | `getBySlug`, `getAllWithProductCount` |
| `ProductDao` | `products` | `getAllWithCategory`, `getByCategoryId`, `searchByName` |
| `ProductVariantDao` | `product_variants` | `getByProductId`, `getBySku`, `decreaseStock` |
| `CartItemDao` | `cart_items` | `getByUserId`, `getByUserAndVariant`, `clearForUser` |
| `WishlistItemDao` | `wishlist_items` | `getByUserId` |
| `OrderDao` | `orders` | `getAllWithCustomer`, `getByUserId`, `getByStatus` |
| `OrderItemDao` | `order_items` | `getByOrderId` |
| `ReviewDao` | `reviews` | `getByProductId`, `getRatingSummary` |

## Setup

1. Start Apache and MySQL in XAMPP.
2. Import `database/varenne.sql` in phpMyAdmin (Import tab), or run `mysql -u root < backend/database/varenne.sql`.
   It creates the `varenne` database and fills it with sample data.
3. Copy `config/config.example.php` to `config/config.php` and set your database user and password.
4. Run `composer install` in `backend/` (installs FlightPHP and swagger-php into `vendor/`).
5. Run `php backend/tests/dao_test.php` (or open it in the browser). It should end with `0 failed`.
   The test runs in a transaction that is rolled back, so the sample data stays the same.
6. With Apache running, run `php backend/tests/api_test.php`. It should end with `0 failed`.

## Services (Milestone 3)

Every service extends `BaseService`, which gives `getAll()`, `getById()` (404 if missing), `add()`, `update()`
(partial: only the fields that are sent) and `delete()`. Each service overrides `validate($data, $current)` with
the rules of its table. Problems are reported with `ApiException($message, $status)`; `index.php` turns them into
`{"error": "..."}` with that status code.

| Service | Main rules |
|---|---|
| `UserService` | names and valid unique email required; `password` (8+ chars) is hashed, the hash is never returned; role is `customer` or `admin` |
| `AddressService` | street, city, postal code, country required; the user must exist; only one default address per user |
| `CategoryService` | name required; slug made from the name; name/slug unique; cannot delete a category that has products |
| `ProductService` | category must exist; price > 0; filter by `category_id` or `search`; an ordered product cannot be deleted (hide it) |
| `ProductVariantService` | size, colour, SKU required; SKU unique; stock is a whole number >= 0 |
| `CartItemService` | quantity >= 1 and not above stock; adding the same variant again raises the quantity; bag total worked out here |
| `WishlistItemService` | user and product must exist; the same product only once |
| `OrderService` | checkout: bag -> order in one transaction, prices and total from the database, stock lowered, bag emptied; status must be a known value |
| `OrderItemService` | order and variant must exist; quantity >= 1; unit price defaults to the product price; every change recalculates the order total |
| `ReviewService` | rating 1 to 5; one review per user and product |
| `CurrencyService` | external API: Frankfurter rates with a 5 s timeout; failure is logged and gives 503 |

## Endpoints

Base URL: `http://localhost/MalikIslamovic/Foundations-of-Frontend/backend`. Full details and "Try it out" are in Swagger
(`public/v1/docs/`).

| Entity | Endpoints |
|---|---|
| Users | `GET /users` (`?role=`), `GET /users/{id}`, `POST /users`, `PUT /users/{id}`, `DELETE /users/{id}` |
| Addresses | `GET /addresses`, `GET /users/{id}/addresses`, `GET /addresses/{id}`, `POST /addresses`, `PUT /addresses/{id}`, `DELETE /addresses/{id}` |
| Categories | `GET /categories`, `GET /categories/{id}`, `POST /categories`, `PUT /categories/{id}`, `DELETE /categories/{id}` |
| Products | `GET /products` (`?category_id=`, `?search=`), `GET /products/{id}`, `POST /products`, `PUT /products/{id}`, `DELETE /products/{id}` |
| Variants | `GET /products/{id}/variants`, `GET /variants`, `GET /variants/{id}`, `POST /variants`, `PUT /variants/{id}`, `DELETE /variants/{id}` |
| Cart | `GET /cart-items`, `GET /users/{id}/cart`, `DELETE /users/{id}/cart`, `GET /cart-items/{id}`, `POST /cart-items`, `PUT /cart-items/{id}`, `DELETE /cart-items/{id}` |
| Wishlist | `GET /wishlist-items`, `GET /users/{id}/wishlist`, `GET /wishlist-items/{id}`, `POST /wishlist-items`, `PUT /wishlist-items/{id}`, `DELETE /wishlist-items/{id}` |
| Orders | `GET /orders` (`?status=`), `GET /users/{id}/orders`, `GET /orders/{id}` (with items), `POST /orders` (checkout), `PUT /orders/{id}`, `DELETE /orders/{id}` |
| Order items | `GET /order-items`, `GET /orders/{id}/items`, `GET /order-items/{id}`, `POST /order-items`, `PUT /order-items/{id}`, `DELETE /order-items/{id}` |
| Reviews | `GET /products/{id}/reviews`, `GET /reviews`, `GET /reviews/{id}`, `POST /reviews`, `PUT /reviews/{id}`, `DELETE /reviews/{id}` |
| Currency | `GET /products/{id}/prices` (external API) |

Until JWT login exists (Milestone 4), the customer is sent as `user_id` in the request body or path. The error
handler already passes any `ApiException` status through, so the auth middleware can return 401 and 403 the same way.

The external API (Frankfurter) needs no API key; its base URL is `EXCHANGE_API_URL` in `config/config.php`. If a
provider with a key is used later, the key goes into `config.php` as well, never into the code.

Example:

```bash
curl -X POST http://localhost/MalikIslamovic/Foundations-of-Frontend/backend/cart-items \n     -H "Content-Type: application/json" -d '{"user_id": 2, "variant_id": 9, "quantity": 1}'
```
