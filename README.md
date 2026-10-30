# Varenne: Luxury Clothing Web Shop

Web Programming course project. Varenne is an online boutique for a luxury clothing brand:
customers browse the collection, view products, manage a shopping bag, check out and track orders;
admins manage products, categories and orders from a dashboard.

> **Status:** Milestone 2 (MySQL database and DAO layer). The frontend still uses mock data until Milestone 4.

## Tech stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, vanilla JavaScript (hash-routed SPA) |
| Backend | PHP 8, PDO, MySQL/MariaDB. DAO layer done in Milestone 2; FlightPHP routes and services in Milestone 3 |
| External API (planned) | Currency conversion via a public exchange-rate API (Frankfurter) |
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
│   ├── tests/              # dao_test.php (CRUD check for every DAO)
│   ├── routes/  services/  middleware/
│   ├── config/             # config.example.php committed, config.php ignored
│   ├── logs/               # ignored by Git
│   └── index.php
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

**External API feature:** the dashed box marked `data-external-api="currency-converter"` on the product
page is where converted prices will appear once the backend calls the exchange-rate API.

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
3. Check the DAO layer: `php backend/tests/dao_test.php` should print `0 failed`.

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
- [ ] **M3:** Services, FlightPHP routes, OpenAPI/Swagger, external API (Frankfurter)
- [ ] **M4:** JWT authentication, roles, middleware, logging, connected frontend
- [ ] **M5:** Frontend MVC, validations, deployment

## AI usage

AI assistant rules are in [AGENTS.md](AGENTS.md); usage is logged in [docs/AI_LOG.md](docs/AI_LOG.md).
