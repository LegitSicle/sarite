<?php
namespace sarite;

define("ROOT", dirname(__DIR__));
require_once __DIR__ . "/bootstrap.php";
use sarite\data\Database;
use function in_array;

$dbConfig = [
    "host" => $_ENV["DB_HOST"],
    "port" => $_ENV["DB_PORT"],
    "name" => $_ENV["DB_NAME"],
    "user" => $_ENV["DB_USER"],
    "password" => $_ENV["DB_PASSWORD"],
];
if (in_array("--init_db", $argv, true)) {
    Database::initialize($dbConfig);
}
$database = new Database(ROOT, $dbConfig);
$database->migrate();