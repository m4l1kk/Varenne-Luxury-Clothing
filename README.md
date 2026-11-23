# Varenne: Luxury Clothing Web Shop

Web Programming course project. Varenne is an online boutique for a luxury clothing brand:
customers browse the collection, view products, manage a shopping bag, check out and track orders;
admins manage products, categories and orders from a dashboard.

> **Status:** Milestone 3 (REST API with FlightPHP, services, OpenAPI/Swagger docs and the Frankfurter currency converter).
> Most pages still use mock data until the frontend is connected in Milestone 4.

## Tech stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, vanilla JavaScript (hash-routed SPA) |
| Backend | PHP 8, FlightPHP 3 (REST routes), PDO, MySQL/MariaDB, Composer |
| API docs | OpenAPI 3 generated with swagger-php, shown with Swagger UI |
| External API | Frankfurter (ECB exchange rates) for the product price in other currencies |
| Local server | XAMPP (Apache + MySQL) |

## Project structure

```
Foundations-of-Frontend/
├── frontend/
│   ├── index.html          # SPA shell: navbar, <main id="app">, footer
│   ├── css/style.css       # Theme (black / ivory / gold)
│   ├── js/app.js           # View loader + hash router
│   ├── js/data.js          # Mock data until the backend exists
│   ├── views/              # One HTML template per view (home, shop, product, cart, ...)
│   └── assets/img/         # Static assets
├── backend/
│   ├── dao/                # Database.php, BaseDao.php and one DAO per table
│   ├── database/           # varenne.sql (schema + sample data)
│   ├── services/           # Business logic and validation, one service per entity
│   ├── routes/             # FlightPHP routes with OpenAPI comments, one file per entity
│   ├── public/v1/docs/     # Swagger UI (index.html) and swagger.php (OpenAPI JSON)
│   ├── utils/Logger.php    # Writes logs/info.log and logs/error.log
│   ├── tests/              # dao_test.php (DAO layer) and api_test.php (every endpoint over HTTP)
│   ├── middleware/         # JWT and role checks (Milestone 4)
│   ├── config/             # config.example.php committed, config.php ignored
│   ├── logs/               # ignored by Git
│   ├── composer.json       # flightphp/core, zircote/swagger-php (vendor/ is not committed)
│   └── index.php           # Starts Flight, registers services, loads routes, handles errors
├── docs/
│   ├── ERD.md              # Entity-relationship diagram and keys
│   └── AI_LOG.md           # AI assistant usage log
├── .github/
│   ├── agents/             # Frontend, Backend and Reviewer agents
│   └── skills/update-ai-log/
├── AGENTS.md               # Rules for AI assistants working in this repo
└── .gitignore
```

## How the SPA works

- `index.html` contains only the shell. When the page first opens, `js/app.js` fetches **every** file in
  `views/` once and injects each one as a `<section id="viewName">`.
- Navbar links use IDs (`#home`, `#shop`, `#about`, `#contact`, ...). On `hashchange` the router only
  shows the matching section and hides the others, so there are no page reloads and no repeated requests.
- Product pages use `#product/{id}`.

| View | Hash | Purpose |
|---|---|---|
| Home | `#home` | Hero, categories, featured products |
| Collection | `#shop` | Product grid with category filters |
| Product | `#product/{id}` | Details, sizes, colours, reviews, **external API slot** |
| Bag | `#cart` | Cart items, summary, checkout form |
| Sign in / Register | `#login`, `#register` | Auth forms with validation |
| Profile | `#profile` | Details, order history, addresses, wishlist |
| Dashboard | `#dashboard` | Admin stats, orders, products, categories |
| About / Contact | `#about`, `#contact` | Brand story, client care form |

**External API feature:** on the product page, the box marked `data-external-api="currency-converter"` lets the
customer pick USD, GBP, CHF or BAM. `js/app.js` calls `GET backend/products/{id}/prices` (AJAX); the backend asks
[Frankfurter](https://frankfurter.dev) for today's ECB rates (5 second timeout) and works out BAM from its fixed rate
(1 EUR = 1.95583 BAM). If Frankfurter is down, the failure is written to `backend/logs/error.log`, the API answers
503 with a friendly message, the page shows it and the shop keeps working in EUR.

## Running locally

1. Clone the repository into `C:\xampp\htdocs\` (or any web server root).
2. Start Apache in XAMPP.
3. Open `http://localhost/<path-to-repo>/frontend/`.

The views are loaded with `fetch()`, so the site must be opened through a web server, not by
double-clicking `index.html` (`file://` blocks the requests).

### Database and backend

1. Start MySQL in XAMPP and import `backend/database/varenne.sql` in phpMyAdmin (Import tab).
   This creates the `varenne` database with sample data.
2. Copy `backend/config/config.example.php` to `backend/config/config.php` and fill in your database
   user and password. `config.php` is git-ignored.
3. Install the PHP packages: in `backend/`, run `composer install` (creates `backend/vendor/`).
4. Check the DAO layer: `php backend/tests/dao_test.php` should print `0 failed`.
5. Check the API (Apache and MySQL running): `php backend/tests/api_test.php` should print `0 failed`.

The API answers at `http://localhost/<path-to-repo>/backend/` (for example `GET /products`); `backend/.htaccess`
sends every request to `index.php`, so Apache needs `mod_rewrite` (on by default in XAMPP).

### API documentation (Swagger)

Open `http://localhost/<path-to-repo>/backend/public/v1/docs/`. Every endpoint can be tried there with
"Try it out". The OpenAPI JSON itself is at `backend/public/v1/docs/swagger.php`.

## Test accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@varenne.com` | `Admin123!` |
| Customer | `amina@example.com` | `Customer123!` |

The other sample customers (`luca@`, `sara@`, `emir@example.com`) use the same customer password.
Login itself is added in Milestone 4.

## Database

10 entities: users, addresses, categories, products, product_variants, cart_items, wishlist_items,
orders, order_items, reviews. The diagram, foreign keys and delete rules are in [docs/ERD.md](docs/ERD.md);
the SQL is in [backend/database/varenne.sql](backend/database/varenne.sql). The DAO classes are described in
[backend/README.md](backend/README.md).

## Milestones

- [x] **M1:** Project structure, static SPA, draft ERD, README, AI setup
- [x] **M2:** MySQL database with sample data, DAO layer with a base DAO and CRUD for all 10 entities
- [x] **M3:** Services, FlightPHP routes, OpenAPI/Swagger, external API (Frankfurter)
- [ ] **M4:** JWT authentication, roles, middleware, logging, connected frontend
- [ ] **M5:** Frontend MVC, validations, deployment

## AI usage

AI assistant rules are in [AGENTS.md](AGENTS.md); usage is logged in [docs/AI_LOG.md](docs/AI_LOG.md).
