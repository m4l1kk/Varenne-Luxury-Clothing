# Backend

PHP backend for Varenne. Only the folder structure exists in Milestone 1.

| Folder | Purpose |
|---|---|
| `routes/` | HTTP route definitions (one file per entity, e.g. `ProductRoutes.php`) |
| `services/` | Business logic and validation (`ProductService.php`) |
| `dao/` | Database access with PDO (`BaseDao.php`, `ProductDao.php`) |
| `middleware/` | Auth / role checks, request logging |
| `config/` | `config.example.php` is committed; copy it to `config.php` (ignored by Git) |
| `logs/` | Runtime logs, ignored by Git (only `.gitkeep` is tracked) |
