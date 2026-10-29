<?php
// Copy this file to config.php and fill in your local values.
// config.php is listed in .gitignore so secrets never reach GitHub.

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'varenne');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

define('JWT_SECRET', 'change-me');
define('EXCHANGE_API_URL', 'https://api.frankfurter.app'); // external API (currency conversion)
