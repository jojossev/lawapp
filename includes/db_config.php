<?php
// Configuration de la base de données
$cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL") ?: "mysql://root:@localhost/lawapp");

define('DB_HOST', $cleardb_url["host"] ?? 'localhost');
define('DB_NAME', substr($cleardb_url["path"], 1) ?? 'lawapp');
define('DB_USER', $cleardb_url["user"] ?? 'root');
define('DB_PASS', $cleardb_url["pass"] ?? '');

// Configuration de l'URL de base
$app_url = getenv("APP_URL") ?: "http://localhost/LawApp";
define('BASE_URL', $app_url);
?>
