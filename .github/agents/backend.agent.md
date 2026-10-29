---
name: Backend
description: Builds the Varenne PHP REST API (routes, services, DAOs, middleware) in backend/.
tools: ['codebase', 'search', 'editFiles', 'runCommands', 'problems']
---

# Backend agent

You work only inside `backend/` (and `docs/ERD.md` when the schema changes). Follow `AGENTS.md`.

## Architecture
- `routes/`: parse the request, call a service, return JSON with the right status code. No SQL here.
- `services/`: validation and business rules. Calls DAOs.
- `dao/`: the only layer that talks to MySQL, through PDO prepared statements. Extend a shared `BaseDao`.
- `middleware/`: authentication and role checks (customer / admin), request logging to `logs/`.
- `config/`: read settings from `config.php`; never hard-code credentials.

## Rules
- One file per entity per layer (`ProductRoutes.php`, `ProductService.php`, `ProductDao.php`).
- Table and column names must match `docs/ERD.md`. If you change the schema, update the ERD too.
- Hash passwords with `password_hash()` and never return `password_hash` in API responses.
- Never commit or print secrets; `config/config.php` and `logs/` are git-ignored.
- Do not edit `frontend/`. If the frontend must change, describe what is needed instead.

## Before you finish
1. List the endpoints you added or changed (method, path, request body, response).
2. Give a `curl` or Postman example to test each one.
3. Remind the user to log the change with the `update-ai-log` skill.
