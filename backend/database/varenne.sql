-- Varenne: database schema and sample data (Milestone 2)
-- Import in phpMyAdmin (Import tab) or from a terminal:
--   mysql -u root < backend/database/varenne.sql
-- The script drops and recreates every table, so it can be run again at any time.

CREATE DATABASE IF NOT EXISTS varenne CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE varenne;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS wishlist_items;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Tables
-- ---------------------------------------------------------------------------

CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name    VARCHAR(50)  NOT NULL,
  last_name     VARCHAR(50)  NOT NULL,
  email         VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone         VARCHAR(30)  NULL,
  role          ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE addresses (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NOT NULL,
  street      VARCHAR(150) NOT NULL,
  city        VARCHAR(80)  NOT NULL,
  postal_code VARCHAR(20)  NOT NULL,
  country     VARCHAR(80)  NOT NULL,
  is_default  TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(60)  NOT NULL,
  slug        VARCHAR(60)  NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  name        VARCHAR(120) NOT NULL,
  description TEXT NULL,
  price       DECIMAL(10, 2) NOT NULL,
  image_url   VARCHAR(255) NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  -- RESTRICT: a category that still has products cannot be deleted.
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_variants (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  size       VARCHAR(20) NOT NULL,
  color      VARCHAR(30) NOT NULL,
  sku        VARCHAR(40) NOT NULL,
  stock      INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_variants_sku (sku),
  CONSTRAINT fk_variants_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  variant_id INT UNSIGNED NOT NULL,
  quantity   INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  -- The same size/colour appears only once in a bag; adding it again raises the quantity.
  UNIQUE KEY uq_cart_user_variant (user_id, variant_id),
  CONSTRAINT fk_cart_user    FOREIGN KEY (user_id)    REFERENCES users (id)            ON DELETE CASCADE,
  CONSTRAINT fk_cart_variant FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wishlist_items (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  added_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_wishlist_user_product (user_id, product_id),
  CONSTRAINT fk_wishlist_user    FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  address_id INT UNSIGNED NULL,
  status     ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  total      DECIMAL(10, 2) NOT NULL,
  currency   CHAR(3) NOT NULL DEFAULT 'EUR',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  -- RESTRICT: a user with orders cannot be deleted, so the order history is kept.
  CONSTRAINT fk_orders_user    FOREIGN KEY (user_id)    REFERENCES users (id)     ON DELETE RESTRICT,
  -- SET NULL: deleting an address keeps the order, it just loses the link.
  CONSTRAINT fk_orders_address FOREIGN KEY (address_id) REFERENCES addresses (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id   INT UNSIGNED NOT NULL,
  variant_id INT UNSIGNED NOT NULL,
  quantity   INT UNSIGNED NOT NULL,
  unit_price DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_order_items_order   FOREIGN KEY (order_id)   REFERENCES orders (id)           ON DELETE CASCADE,
  CONSTRAINT fk_order_items_variant FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  rating     TINYINT UNSIGNED NOT NULL,
  comment    TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  -- One review per user per product.
  UNIQUE KEY uq_reviews_user_product (user_id, product_id),
  CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_reviews_user    FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Sample data
-- Test accounts: admin@varenne.com / Admin123!   and   amina@example.com / Customer123!
-- (every customer below uses the password Customer123!)
-- ---------------------------------------------------------------------------

INSERT INTO users (id, first_name, last_name, email, password_hash, phone, role, created_at) VALUES
(1, 'Malik', 'Islamovic', 'admin@varenne.com', '$2y$10$CNNbRx7vYrlotcT4dR0WMOvGeCI3iI9Hwsb0iVKAQPtGZ/WCiRM.6', '+387 61 000 001', 'admin',    '2026-09-01 09:00:00'),
(2, 'Amina',  'Hodzic',    'amina@example.com', '$2y$10$t4h4NL85mf28F0PebY5VdeltJpNmABKAwvTxh3vfb0FyAkltwst8.', '+387 61 111 222', 'customer', '2026-09-15 12:30:00'),
(3, 'Luca',   'Rossi',     'luca@example.com',  '$2y$10$t4h4NL85mf28F0PebY5VdeltJpNmABKAwvTxh3vfb0FyAkltwst8.', '+39 333 123 4567', 'customer', '2026-09-20 18:10:00'),
(4, 'Sara',   'Kovac',     'sara@example.com',  '$2y$10$t4h4NL85mf28F0PebY5VdeltJpNmABKAwvTxh3vfb0FyAkltwst8.', NULL,              'customer', '2026-10-01 08:45:00'),
(5, 'Emir',   'Delic',     'emir@example.com',  '$2y$10$t4h4NL85mf28F0PebY5VdeltJpNmABKAwvTxh3vfb0FyAkltwst8.', '+387 62 333 444', 'customer', '2026-10-03 21:05:00');

INSERT INTO addresses (id, user_id, street, city, postal_code, country, is_default) VALUES
(1, 2, 'Ferhadija 12',        'Sarajevo', '71000', 'Bosnia and Herzegovina', 1),
(2, 2, 'Zmaja od Bosne 8',    'Sarajevo', '71000', 'Bosnia and Herzegovina', 0),
(3, 3, 'Via della Spiga 21',  'Milano',   '20121', 'Italy',                  1),
(4, 4, 'Ilica 45',            'Zagreb',   '10000', 'Croatia',                1),
(5, 5, 'Marsala Tita 30',     'Sarajevo', '71000', 'Bosnia and Herzegovina', 1);

INSERT INTO categories (id, name, slug, description) VALUES
(1, 'Women',       'women',       'Coats, dresses and tailoring for women.'),
(2, 'Men',         'men',         'Suits, knitwear and footwear for men.'),
(3, 'Accessories', 'accessories', 'Leather goods, silk scarves and hats.');

INSERT INTO products (id, category_id, name, description, price, image_url, is_active, created_at) VALUES
(1, 1, 'Cashmere Wrap Coat',    'Double-faced Mongolian cashmere, hand-finished seams and a softly structured shoulder.', 2450.00, NULL, 1, '2026-10-01 10:00:00'),
(2, 1, 'Silk Slip Dress',       'Bias-cut silk charmeuse with an adjustable cowl neckline.',                             1290.00, NULL, 1, '2026-09-10 10:00:00'),
(3, 2, 'Tailored Wool Suit',    'Super 150s Italian wool, half-canvassed jacket with peak lapels.',                       3100.00, NULL, 1, '2026-09-05 10:00:00'),
(4, 2, 'Merino Rollneck',       'Extra-fine merino knitted in a dense, smooth gauge.',                                     540.00, NULL, 1, '2026-09-05 10:00:00'),
(5, 3, 'Calfskin Tote',         'Vegetable-tanned calfskin with a suede-lined interior and gold-tone hardware.',          1850.00, NULL, 1, '2026-09-20 10:00:00'),
(6, 2, 'Leather Chelsea Boot',  'Goodyear-welted boot in polished box calf.',                                              890.00, NULL, 1, '2026-09-12 10:00:00'),
(7, 3, 'Silk Twill Scarf',      '90 x 90 cm silk twill, hand-rolled edges, archive print.',                                420.00, NULL, 1, '2026-10-05 10:00:00'),
(8, 1, 'Pleated Wide Trousers', 'High-waisted wool crepe with front pleats and a fluid leg.',                              780.00, NULL, 1, '2026-09-18 10:00:00'),
(9, 3, 'Wool Fedora Hat',       'Felted wool with a grosgrain ribbon band and a structured brim.',                         320.00, NULL, 1, '2026-10-10 10:00:00');

INSERT INTO product_variants (id, product_id, size, color, sku, stock) VALUES
(1,  1, 'S',        'Camel',    'VAR-001-S-CAM',   4),
(2,  1, 'M',        'Camel',    'VAR-001-M-CAM',   3),
(3,  1, 'M',        'Black',    'VAR-001-M-BLK',   2),
(4,  2, 'XS',       'Black',    'VAR-002-XS-BLK',  5),
(5,  2, 'S',        'Black',    'VAR-002-S-BLK',   6),
(6,  2, 'S',        'Burgundy', 'VAR-002-S-BUR',   3),
(7,  3, '48',       'Navy',     'VAR-003-48-NAV',  2),
(8,  3, '50',       'Navy',     'VAR-003-50-NAV',  2),
(9,  4, 'M',        'Ivory',    'VAR-004-M-IVO',  10),
(10, 4, 'L',        'Black',    'VAR-004-L-BLK',   8),
(11, 5, 'One size', 'Cognac',   'VAR-005-OS-COG',  3),
(12, 6, '42',       'Black',    'VAR-006-42-BLK',  4),
(13, 6, '43',       'Brown',    'VAR-006-43-BRN',  4),
(14, 7, 'One size', 'Gold',     'VAR-007-OS-GLD', 12),
(15, 7, 'One size', 'Navy',     'VAR-007-OS-NAV',  9),
(16, 8, '36',       'Ivory',    'VAR-008-36-IVO',  5),
(17, 8, '38',       'Black',    'VAR-008-38-BLK',  5),
(18, 9, 'M',        'Brown',    'VAR-009-M-BRN',   6);

INSERT INTO cart_items (id, user_id, variant_id, quantity) VALUES
(1, 2, 5,  1),
(2, 2, 14, 2),
(3, 3, 9,  1);

INSERT INTO wishlist_items (id, user_id, product_id, added_at) VALUES
(1, 2, 1, '2026-10-12 19:00:00'),
(2, 2, 5, '2026-10-13 20:15:00'),
(3, 4, 7, '2026-10-14 09:30:00');

INSERT INTO orders (id, user_id, address_id, status, total, currency, created_at) VALUES
(1, 2, 1, 'delivered',  1710.00, 'EUR', '2026-10-08 14:20:00'),
(2, 5, 5, 'cancelled',  3100.00, 'EUR', '2026-10-09 11:05:00'),
(3, 4, 4, 'delivered',  1850.00, 'EUR', '2026-10-10 16:40:00'),
(4, 3, 3, 'processing',  540.00, 'EUR', '2026-10-11 10:15:00'),
(5, 2, 1, 'shipped',    3740.00, 'EUR', '2026-10-12 17:55:00');

INSERT INTO order_items (id, order_id, variant_id, quantity, unit_price) VALUES
(1, 1, 5,  1, 1290.00),
(2, 1, 14, 1,  420.00),
(3, 2, 7,  1, 3100.00),
(4, 3, 11, 1, 1850.00),
(5, 4, 9,  1,  540.00),
(6, 5, 1,  1, 2450.00),
(7, 5, 6,  1, 1290.00);

INSERT INTO reviews (id, user_id, product_id, rating, comment, created_at) VALUES
(1, 2, 2, 5, 'The silk is beautiful and the fit is perfect.',      '2026-10-15 12:00:00'),
(2, 2, 7, 4, 'Lovely colours, a little smaller than I expected.', '2026-10-15 12:10:00'),
(3, 4, 5, 5, 'Worth every cent. The leather smells wonderful.',   '2026-10-16 08:30:00'),
(4, 3, 4, 4, 'Very soft, and it keeps its shape after washing.',  '2026-10-18 19:45:00');
