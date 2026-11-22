# AGENTS.md: Varenne

Instructions for AI coding assistants (GitHub Copilot, Antigravity, Claude, etc.) working in this repository.
Read this file before making any change.

## Project

Varenne is a web shop for a luxury clothing brand, built for the Web Programming course.
Customers browse products, manage a bag and place orders; admins manage products, categories and orders.

- `frontend/`: static single-page application (HTML, Bootstrap 5.3, vanilla JS). No framework, no build step.
- `backend/`: PHP REST API on FlightPHP (Milestone 3) in layers: `routes/` → `services/` → `dao/`, plus `middleware/`,
  `utils/` (`Logger.php`) and `config/`. Dependencies come from Composer (`backend/composer.json`); `vendor/` is not committed.
- `backend/public/v1/docs/`: Swagger UI and `swagger.php`, which builds the OpenAPI document from the `@OA` comments.
- `backend/database/varenne.sql`: the MySQL schema and sample data (Milestone 2).
- `docs/`: `ERD.md` (database design and keys) and `AI_LOG.md` (log of AI usage, required by the course).

## Frontend rules

1. **SPA contract.** `frontend/index.html` is only the shell. Each page is a file in `frontend/views/<name>.html`
   and must be listed in the `VIEWS` array in `frontend/js/app.js`. Views are fetched **once** at startup;
   never fetch a view again on navigation and never add full page reloads.
2. Navigation uses hash IDs (`#shop`, `#product/3`). Links must be `href="#viewName"`; never use `href="#"`
   (it routes to home).
3. Views are HTML fragments: no `<html>`, `<head>`, `<body>` or `<script>` tags inside `views/`.
   JavaScript goes in `frontend/js/`.
4. Content injected after load must use event delegation (`document.addEventListener` + `closest()`).
5. Theme: use the CSS variables in `css/style.css` (`--mn-black`, `--mn-ivory`, `--mn-gold`, ...) and Bootstrap
   utilities. Do not hard-code new colours or add another CSS framework.
6. Every page must work at 375 px width with no horizontal scrolling.
7. AJAX calls go to `API_BASE` (`../backend`) in `js/app.js`, and only to endpoints that exist in `backend/routes/`
   and in Swagger. Never invent API URLs. Pages that are not connected yet still read `js/data.js` (until Milestone 4).
8. The external API feature lives in the element marked `data-external-api="currency-converter"` on the
   product view. Keep that marker.

## Backend rules (Milestone 2+)

1. Layering is strict: routes only parse the request and call a service; services hold validation and
   business logic; DAOs are the only place that talks to the database (PDO, prepared statements only).
2. One file per entity per layer, e.g. `ProductRoutes.php`, `ProductService.php`, `ProductDao.php`.
3. Table and column names follow `docs/ERD.md` (snake_case, plural tables). If the schema changes, update the ERD in the same change.
4. Secrets live in `backend/config/config.php`, which is git-ignored. Only `config.example.php` is committed.
   Never print, log or commit real credentials.
5. Passwords are stored with `password_hash()`; never store or return plain text passwords.

### Database and DAO rules (Milestone 2)

6. `backend/database/varenne.sql` is the single source of the schema. A schema change goes into that file,
   `docs/ERD.md` and the DAO's column list in the same commit. Keep sample data consistent (order totals
   equal the sum of their `order_items`).
7. Every DAO extends `BaseDao` and calls `parent::__construct('<table>', [<writable columns>])`. Do not
   list `id` or `created_at` as writable. Do not copy the CRUD methods into a child DAO; only add
   entity-specific queries.
8. SQL values are always bound with named placeholders (`:email`). Never put a variable into the SQL text,
   except column/table names that come from the DAO's own fixed list. A named placeholder may be used only once
   per statement (emulated prepares are off).
9. Get the connection only through `Database::connect()`; never create another `new PDO(...)`.
10. `UserDao` must never return `password_hash`, except from `getByEmail()` (needed for login).
11. DAOs do not validate input or decide permissions. Validation belongs to services, permissions to middleware (Milestone 4).
12. After changing a DAO, run `php backend/tests/dao_test.php` and add a check for any new method.

### Service, route and API rules (Milestone 3)

13. Every service extends `BaseService` and overrides `validate($data, $current)`; `$current` is `null` on create
    and the existing row on update (partial update: only the fields that are sent are checked). Validate types,
    required fields, ranges and that foreign keys exist, and return only the fields that may be saved.
14. A service reports an expected problem by throwing `ApiException($message, $status)`: 400 invalid input,
    404 not found, 409 conflict (duplicate, not enough stock, still in use), 503 external API down. Never return
    `null` or `false` to a route. Services never write SQL and never call `Flight::`.
15. Routes only read the request (`Flight::request()`), call one service method and return `Flight::json()`
    (201 for a created row). One route file per entity, named `<Entity>Routes.php`; `index.php` loads them all.
16. Every route has an `@OA` comment (path, tags, parameters, request body with an example, every status code it
    can return). After adding a route, open `backend/public/v1/docs/` and check it appears in Swagger.
17. Errors are handled once, in the `error` handler in `index.php`: clients get JSON `{"error": "..."}` and never
    a stack trace, SQL or file path; unexpected errors are written to `backend/logs/error.log` with `Logger`.
    Inside Flight handlers use `Flight::jsonHalt()`, because `Flight::json()` is not sent from the error handler.
18. Prices, totals and stock are always taken from the database, never from the request body (see checkout
    in `OrderService::add()`). Writes that must succeed together run in one transaction (`beginTransaction()`,
    `commit()`, `rollBack()` on `BaseDao`).
19. External API calls (Frankfurter, in `CurrencyService`) go through the backend only, with a timeout, an error
    log entry on failure and a friendly 503 message; the shop must keep working when the API is down. The base URL
    comes from `EXCHANGE_API_URL` in `config.php`.
20. After changing a service or route, run `php backend/tests/api_test.php` (Apache and MySQL running) and add
    a check for the new behaviour, including at least one error case.

## General rules

- Keep changes small and focused; do not reformat files you were not asked to touch.
- Do not add dependencies, build tools or frameworks without the student asking for them. Current Composer
  packages: `flightphp/core`, `zircote/swagger-php` 4.x (5.x cannot read comments above `Flight::route()`) and
  `doctrine/annotations`.
- Do not commit `node_modules/`, `vendor/`, `backend/logs/*` or `backend/config/config.php`.
- Write code comments and commit messages in English, in the imperative ("Add cart view").
- Work on the current milestone branch (`milestone1`, `milestone2`, ...), never directly on `main`.
- After any meaningful AI-assisted change, add an entry to `docs/AI_LOG.md` (use the `update-ai-log` skill).

## Agents and skills in this repo

| File | Use it for |
|---|---|
| `.github/agents/frontend.agent.md` | Views, styling, SPA routing |
| `.github/agents/backend.agent.md` | PHP routes, services, DAOs, database |
| `.github/agents/reviewer.agent.md` | Reviewing a diff against these rules (read-only) |
| `.github/skills/update-ai-log/SKILL.md` | Adding an entry to `docs/AI_LOG.md` |

## How to verify a change

- Frontend: start Apache (XAMPP), open `http://localhost/MalikIslamovic/Foundations-of-Frontend/frontend/`,
  click through every navbar link, check the browser console has no errors, and check the Network tab shows
  each `views/*.html` requested only once.
- Database: re-import `backend/database/varenne.sql` in phpMyAdmin; it must run without errors.
- DAO layer: `php backend/tests/dao_test.php` must end with `0 failed`.
- API (Milestone 3): `php backend/tests/api_test.php` must end with `0 failed`. It calls every endpoint over HTTP,
  creates its own test rows and deletes them again.
- Swagger: open `http://localhost/MalikIslamovic/Foundations-of-Frontend/backend/public/v1/docs/`, try the new
  endpoint with "Try it out" and check the status code.
