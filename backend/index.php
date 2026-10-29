<?php
// Backend entry point. Routing (FlightPHP) and the REST API are added in Milestone 2.
// Planned flow: routes/ -> services/ (business logic) -> dao/ (database access).

require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => 'Varenne API placeholder']);
