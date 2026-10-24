<?php
namespace sarite;
use sarite\data\Database;
use sarite\web\Router;
use sarite\web\Request;
use sarite\web\Viewer;
use sarite\web\Response;
use Exception;

class Application {
    public readonly Router $router;
    public readonly Database $database;
    public function __construct(array $config) {
        $this->router = new Router();
        $this->database = new Database($config["database"]);

        $viewPath = $config["viewPath"];
        $layoutPath = $config["layoutPath"] ?? "{$viewPath}layouts" . DIRECTORY_SEPARATOR;
        Viewer::setPaths($viewPath, $layoutPath);

        if (isset($config["routes"])) {
            $routesLoader = require_once $config["routes"];
            $routesLoader($this);
        }
    }

    public function run(): void {
        try {
            $request = new Request();
            $response = $this->router->resolve($request);
            $response->send();
        } catch (Exception $e) {
            $this->handleException($e)->send();
        }
    }

    private function handleException(Exception $e): Response {
        $code = $e->getCode();
        $statusCode = ($code >= 400 && $code < 600) ? $code : 500;

        error_log($e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

        $errorView = Viewer::viewExists("_{$statusCode}") ? "_{$statusCode}" : "_404";
        return (new Response())
            ->statusCode($statusCode)
            ->content(Viewer::getContents($errorView, "error", ["error" => "{$statusCode} | {$e->getMessage()}"]));
    }
}