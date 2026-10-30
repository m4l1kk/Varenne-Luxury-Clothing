# AGENTS.md: Varenne

Instructions for AI coding assistants (GitHub Copilot, Antigravity, Claude, etc.) working in this repository.
Read this file before making any change.

## Project

Varenne is a web shop for a luxury clothing brand, built for the Web Programming course.
Customers browse products, manage a bag and place orders; admins manage products, categories and orders.

- `frontend/`: static single-page application (HTML, Bootstrap 5.3, vanilla JS). No framework, no build step.
- `backend/`: PHP REST API (from Milestone 2) in layers: `routes/` → `services/` → `dao/`, plus `middleware/` and `config/`.
- `docs/`: `ERD.md` (database plan) and `AI_LOG.md` (log of AI usage, required by the course).

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
7. Until the backend exists, data comes from `js/data.js`. Do not invent API URLs in the frontend.
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

## General rules

- Keep changes small and focused; do not reformat files you were not asked to touch.
- Do not add dependencies, build tools or frameworks without the student asking for them.
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
- Backend: call the endpoint with Postman or `curl` and check the response and status code.
