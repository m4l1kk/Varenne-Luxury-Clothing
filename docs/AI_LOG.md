# AI Log

How AI assistants were used in this project, what they got right, and where they were wrong.
New entries are added with the `update-ai-log` skill (`.github/skills/update-ai-log/SKILL.md`).

## Milestone 1

### Editor assistant setup
- **Assistant:** GitHub Copilot Chat
- **Editor:** Visual Studio Code
- **Setup note:** The first message failed with "Language model unavailable" because I had not signed in to GitHub in VS Code. After signing in with GitHub, Copilot worked.

### 2026-10-15: Ask mode: does Copilot follow AGENTS.md?
- **Tool / mode:** GitHub Copilot Chat, Ask mode
- **Task:** Check that the assistant reads and uses the project rules.
- **Prompt:** "What are the SPA rules in this project?"
- **Result:** Copilot listed all 8 frontend rules: the shell only in `index.html`, views as fragments registered in `VIEWS`,
  hash routes and never `href="#"`, no `<script>` in views, event delegation with `closest()`, theme variables only,
  375 px without horizontal scroll, `data.js` as the data source, and keeping the `data-external-api="currency-converter"` marker.
  It ended with "This is all from AGENTS.md."
- **Correct?** Yes. Every point matches `AGENTS.md`.
- **What I learned:** Copilot picks up `AGENTS.md` automatically, so the rules I write there shape its answers.

### 2026-10-15: Completions: AI was wrong (broken product entry)
- **Tool / mode:** GitHub Copilot inline completions
- **Task:** Add a 9th product to `PRODUCTS` in `frontend/js/data.js`.
- **Prompt:** Started typing `{ id: 9, name: "` after the last product and accepted the suggestion with Tab.
- **Result:** Copilot suggested a "Wool Fedora Hat" with price, colours and sizes in the right shape, but:
  1. The suggestion did not add a comma after the previous product (`}` instead of `},`) and dropped the opening `{`,
     so `data.js` had a syntax error (`SyntaxError: Unexpected identifier 'id'`). Because the whole file failed,
     no products rendered anywhere on the site.
  2. It used `icon: "bi-hat"`, which does not exist in Bootstrap Icons 1.11.3, so the product tile would show no icon.
- **Correct?** Partly. I fixed the comma and the brace by hand, checked the file with `node --check frontend/js/data.js`,
  and replaced `bi-hat` with `bi-gem`, an icon that exists.
- **What I learned:** Completions only see the text around the cursor, so they can break the surrounding syntax and invent names that look real.
  I have to read what I accept and check the page afterwards.

### 2026-10-15: Agent mode: make the shop "Sort" dropdown work
- **Tool / mode:** GitHub Copilot Chat, Agent mode (Frontend agent)
- **Task:** The Sort dropdown on `#shop` did nothing.
- **Prompt:** "Make the Sort dropdown on the shop page actually sort the products."
- **Result:** The agent changed 2 files:
  - `frontend/views/shop.html`: gave the select an `id="shopSort"` and `value` attributes on each option.
  - `frontend/js/app.js`: added a `SHOP_STATE` object (current category and sort); `renderShop()` now copies the
    list, sorts by price ascending or descending, and keeps the select in sync; category filters keep the
    current sort; a delegated `change` listener on `document` handles `#shopSort`, following the AGENTS.md
    rule on event delegation.
- **Diff review:** I reviewed both files in the VS Code diff view before keeping them. I checked that it did not fetch views again,
  did not add a library, and used event delegation. I kept all the changes. I tested `#shop`: both price
  sorts work and stay applied when switching between Women, Men and Accessories.
- **Correct?** Yes. Small note: "Newest" just keeps the original order from `data.js`, because there is no date field until the backend exists.
- **What I learned:** With the rules in `AGENTS.md` and a scoped Frontend agent, the agent stayed inside `frontend/` and followed the project's patterns.

### 2026-10-15: Scaffold Milestone 1 (structure, SPA, ERD, docs)
- **Tool / mode:** Claude (Claude Code agent, working in the project folder)
- **Task:** Create the frontend/backend structure, `.gitignore`, the hash-routed SPA with all views,
  the draft ERD, README, AGENTS.md, the three agents and the `update-ai-log` skill.
- **Prompt:** The full Milestone 1 requirements from the course page.
- **Result:** All files in this commit. The downloaded SB Admin 2 template was moved to `_template-reference/`
  (git-ignored) because an admin template did not fit a luxury storefront; the SPA was written from scratch on Bootstrap 5.3.
- **Correct?** Partly. See the two cases below.
- **What I learned:** The SPA only works over `http://` (XAMPP), because `fetch()` of the views is blocked on `file://`.

### 2026-10-15: AI was wrong: "Size guide" link sent the user back to Home
- **Tool / mode:** Claude agent
- **Task:** Product view with a size-guide modal.
- **Result:** The AI wrote `<a href="#" data-bs-toggle="modal">Size guide</a>`. In a hash-routed SPA, `href="#"`
  changes the hash to empty, the router treats that as an unknown view and jumps to `#home` instead of opening the modal.
- **Correct?** No. Fixed by changing it to `<button type="button" class="btn btn-link">`, and a rule
  "never use `href=\"#\"`" was added to AGENTS.md so the assistant does not repeat it.
- **What I learned:** Code that is fine on a normal website can break a SPA router; generated links need checking against how routing works.

### 2026-10-15: AI was wrong: link colours ignored the theme
- **Tool / mode:** Claude agent
- **Task:** Black / ivory / gold theme on top of Bootstrap.
- **Result:** The AI overrode `--bs-link-color` and assumed links would turn black. Bootstrap 5.3 actually reads
  `--bs-link-color-rgb`, so breadcrumbs and product names in the bag stayed Bootstrap blue. It was only noticed
  when checking the page in the browser.
- **Correct?** No. Fixed with explicit `a { color: var(--mn-black) }` and `a:hover` rules in `css/style.css`.
- **What I learned:** Always look at the result in the browser; the AI's explanation of a CSS change is not proof that it works.

## Milestone 2

### Tools Used

- **Claude (Claude Code agent):** Assisted with parts of the database schema, DAO implementation, test script, and documentation.
- I reviewed the generated suggestions, tested the SQL in phpMyAdmin, and ran the DAO test script before committing.

### 2026-10-27: Database Schema and DAO Layer

- **Tool / Mode:** Claude (Claude Code agent)
- **Task:** Assist with implementing the MySQL database based on `docs/ERD.md`, including sample data and the DAO layer with CRUD operations.
- **Prompt:** "Review docs/ERD.md and help implement the database schema and DAO layer with prepared statements, sample data, and CRUD tests. Follow AGENTS.md and the existing project structure."
- **Result:**
  - Assisted with `backend/database/varenne.sql`, including the 10 tables, relationships, constraints, and sample data matching `frontend/js/data.js`.
  - Provided suggestions for `Database.php`, `BaseDao.php`, and the 10 entity-specific DAO classes.
  - Helped structure `backend/tests/dao_test.php` to test CRUD operations inside a transaction that is rolled back.
- **Correct?** Partly. The basic CRUD tests passed, but two issues were discovered during additional testing.
- **What I Learned:** Basic tests are not enough to confirm that database operations work correctly with unusual inputs.


### 2026-10-29: AI was wrong: `false` could not be saved to a TINYINT column
- **Tool / mode:** Claude agent
- **Task:** `BaseDao::add()` / `update()` passing the data array to `$stmt->execute($data)`.
- **Result:** `ProductDao->update(1, ['is_active' => false])` threw
  `PDOException: 1366 Incorrect integer value: '' for column products.is_active`. `execute()` sends every value as a
  string, PHP turns `false` into an empty string, and MySQL in strict mode refuses `''` for a number column.
  `true` worked (it becomes `'1'`), so the normal test did not catch it.
- **How it was noticed:** An extra check that hid a product with `is_active => false`.
- **Correct?** No. Fixed in `BaseDao::onlyAllowedColumns()`: booleans are converted to `1` / `0` before
  `execute()`. A check "PHP booleans are saved as 1 / 0" was added to `dao_test.php`.
- **What I learned:** PDO does not know column types when values go through `execute()`; it is worth checking `false`, `0` and empty values.

### 2026-10-29: AI was wrong: a negative quantity increased the stock
- **Tool / mode:** Claude agent
- **Task:** `ProductVariantDao::decreaseStock($id, $quantity)` for checkout.
- **Result:** The query `UPDATE product_variants SET stock = stock - :quantity WHERE id = :id AND stock >= :min_stock`
  correctly refused to sell more than is in stock, but `decreaseStock(1, -5)` returned `true` and raised the stock
  from 4 to 9, because `stock - (-5)` is an addition and `stock >= -5` is always true.
- **How it was noticed:** Reviewing the method and trying a negative number inside a rolled-back transaction.
- **Correct?** No. Fixed by returning `false` when `$quantity <= 0`, and two checks were added to the test
  (negative quantity is refused, a normal quantity lowers the stock). Quantity validation will also go into the
  service in Milestone 3.
- **What I learned:** The DAO should not trust its arguments when a wrong value silently corrupts data.

### AGENTS.md changes in this milestone
- Added "Database and DAO rules": `varenne.sql` is the source of the schema, every DAO extends `BaseDao` with a
  list of writable columns, only named placeholders, one `Database::connect()`, never return `password_hash`,
  and run `dao_test.php` after every DAO change.


## Milestone 3

### Tools Used

- **Claude (Claude Code agent, working in my project folder on my PC):** Used as an assistant for parts of the implementation, including service logic, FlightPHP routes, OpenAPI documentation, currency conversion, and reviewing project documentation. It also helped identify and resolve some issues during testing.
- **My own work:** Reviewed the generated code, checked the implementation against the official milestone requirements, tested API endpoints, investigated errors, and verified the currency converter and Swagger documentation.

### 2026-11-20: REST API, services, Swagger and currency converter

- **Tool / mode:** Claude (Claude Code agent) and manual development.
- **Task:** Implement Milestone 3 requirements, including services with validation, FlightPHP CRUD routes, API documentation and external currency conversion.
- **How AI was used:** Claude assisted with setting up the service structure, writing parts of the CRUD logic, creating route definitions and OpenAPI comments. It also helped with the Frankfurter API integration and some documentation updates.
- **My work:** I reviewed the generated implementation, compared the available endpoints with the official checklist and tested the API using HTTP requests and the provided test scripts.
- **Result:**
  - `backend/services/`: `BaseService` and individual services for the 10 entities, including validation for emails, passwords, prices, ratings, stock and order totals.
  - `backend/routes/`: CRUD routes for all 10 entities, with OpenAPI documentation. Checkout (`POST /orders`) creates an order using a database transaction and database prices.
  - `backend/index.php`: Flight setup and centralized JSON error handling.
  - `backend/public/v1/docs/`: Swagger UI documentation.
  - `CurrencyService` and the currency box on the product page: Integration with Frankfurter, including timeout handling and error messages.
  - `backend/tests/api_test.php`: 81 checks passed, 0 failed. `dao_test.php`: 62 passed, 0 failed.
- **Issues found:** During my review against the official checklist, I noticed that the first implementation was missing some order item functionality and several list and update routes. These gaps were addressed before completing the milestone.
- **What I learned:** AI assistance can speed up development, but the generated code still needs to be reviewed and compared with the actual project requirements.

### 2026-11-20: Error handling issue (200 OK instead of error status)

- **Tool / mode:** Claude assistance and manual testing.
- **Task:** Check the JSON error handling in `backend/index.php`.
- **Problem:** The initial error handler used `Flight::json()` inside `Flight::map('error', ...)`. In FlightPHP 3, this did not properly send the response, resulting in errors such as 400, 404 and 409 being returned as `200 OK` with an empty body.
- **How it was noticed:** While running `api_test.php`, 23 out of 60 checks failed. I also used `curl -i .../products/99999` to investigate the response and confirm the issue.
- **Correction:** The handler was updated to use `Flight::jsonHalt()` and clear the response body before sending the error. After the correction, all 60 checks passed. Rule 17 in `AGENTS.md` was also updated to prevent the same issue.
- **What I learned:** Testing successful requests is not enough. Error responses and HTTP status codes also need to be checked.

### 2026-11-20: Input validation issue (array instead of a single value)

- **Tool / mode:** Manual API testing with Claude assistance.
- **Task:** Check how the services handle invalid JSON input.
- **Problem:** Some validation methods assumed that every submitted field contained a single value. For example, `PUT /categories/3` with `{"slug": [1]}` caused a 500 server error instead of returning a 400 validation error.
- **How it was noticed:** I tested unusual inputs using curl, including text instead of numbers, decimal quantities and arrays instead of text values. The array input exposed the problem.
- **Correction:** `rejectNestedValues()` was added to `BaseService` to reject arrays and objects in fields that expect single values. A corresponding test was also added to `api_test.php`.
- **What I learned:** Input validation must check the actual data type, not just whether a field exists or contains a value.

### 2026-11-20: Library version and external API changes

- **Tool / mode:** Claude assistance and manual verification.
- **Result:**
  1. During the Swagger setup, I checked the compatibility of `swagger-php` with the project's route annotations. Version 5.8 did not properly support the documentation structure used in the project, so version 4.11 with `doctrine/annotations` was used instead. The reason was documented in `AGENTS.md`.
  2. While testing the currency converter, I noticed that the old Frankfurter address (`https://api.frankfurter.app`) returned a `301 Moved Permanently` response. The address was updated to `https://api.frankfurter.dev/v1` in `config.example.php`.
- **What I learned:** Dependencies and external APIs need to be checked for compatibility and availability rather than assuming that the latest version or an older API address will always work.

### AGENTS.md changes in this milestone

- Added service, route and API development rules.
- Documented the use of `BaseService` and `ApiException`.
- Added requirements for OpenAPI documentation on every route.
- Documented centralized error handling in `index.php`.
- Added rules requiring prices and order totals to come from the database.
- Added requirements for external API timeouts, logging and user-friendly error messages.
- Required `api_test.php` to pass after changes.
- Updated frontend rule 7 so AJAX requests use `API_BASE` and only call endpoints documented in Swagger.
