# Backend

PHP backend for Varenne. Milestone 2 added the MySQL database and the DAO layer;
routes and services follow in Milestone 3.

| Folder | Purpose |
|---|---|
| `routes/` | HTTP route definitions (one file per entity, e.g. `ProductRoutes.php`) |
| `services/` | Business logic and validation (`ProductService.php`) |
| `dao/` | Database access with PDO: `Database.php` (connection), `BaseDao.php` (shared CRUD), one DAO per table |
| `database/` | `varenne.sql`: schema and sample data |
| `tests/` | `dao_test.php`: checks CRUD on every DAO (`php backend/tests/dao_test.php`) |
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
4. Run `php backend/tests/dao_test.php` (or open it in the browser). It should end with `0 failed`.
   The test runs in a transaction that is rolled back, so the sample data stays the same.
