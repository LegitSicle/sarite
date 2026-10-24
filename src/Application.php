<?php
namespace sarite;
use sarite\web\Router;
use sarite\data\Database;

class Application {
    public readonly Router $router;
    public readonly Database $database;
    public function __construct(array $config) {
        $root = $config["root"] ?? dirname(__DIR__);
        $this->database = new Database($root, $config["database"]);
        $this->router = new Router();
    }
}