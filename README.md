# Varenne: Luxury Clothing Web Shop

Web Programming course project. Varenne is an online boutique for a luxury clothing brand:
customers browse the collection, view products, manage a shopping bag, check out and track orders;
admins manage products, categories and orders from a dashboard.

> **Status:** Milestone 1 (initial setup, static frontend, database planning). Draft README.

## Tech stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, vanilla JavaScript (hash-routed SPA) |
| Backend (from Milestone 2) | PHP (REST API: routes → services → DAO), MySQL |
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
│   ├── routes/  services/  dao/  middleware/
│   ├── config/             # config.example.php committed, config.php ignored
│   ├── logs/               # ignored by Git
│   └── index.php
├── docs/
│   ├── ERD.md              # Draft entity-relationship diagram
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

Backend config (from Milestone 2): copy `backend/config/config.example.php` to `backend/config/config.php`
and fill in database credentials.

## Database

See the draft ERD in [docs/ERD.md](docs/ERD.md): 10 entities (users, addresses, categories, products,
product_variants, cart_items, wishlist_items, orders, order_items, reviews).

## Milestones

- [x] **M1:** Project structure, static SPA, draft ERD, README, AI setup
- [ ] **M2:** Database + backend CRUD (routes, services, DAO)
- [ ] **M3+:** Connect frontend to backend, authentication, external API

## AI usage

AI assistant rules are in [AGENTS.md](AGENTS.md); usage is logged in [docs/AI_LOG.md](docs/AI_LOG.md).
