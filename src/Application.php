<?php
namespace sarite;
use sarite\data\Database;
use sarite\web\Router;
use sarite\web\Request;
use sarite\web\Viewer;
use sarite\web\Response;
use sarite\data\DatabaseModel;
use sarite\data\UserModel;
use sarite\web\Session;
use Exception;

class Application {
    public readonly Router $router;
    public readonly Session $session;
    public readonly Database $database;
    public readonly ?UserModel $user;
    private string $userClass;
    public function __construct(array $config) {
        $this->router = new Router();
        $this->session = new Session();
        $this->database = new Database([
            "migrations" => $_ENV["DB_MIGRATIONS"],
            "host" => $_ENV["DB_HOST"],
            "port" => $_ENV["DB_PORT"],
            "name" => $_ENV["DB_NAME"],
            "user" => $_ENV["DB_USER"],
            "password" => $_ENV["DB_PASSWORD"],
        ]);
        $this->userClass = $config["userClass"];

        $viewPath = $config["viewPath"];
        $layoutPath = $config["layoutPath"] ?? "{$viewPath}layouts" . DIRECTORY_SEPARATOR;
        Viewer::setPaths($viewPath, $layoutPath);
        DatabaseModel::connectDatabase($this->database);

        if (!class_exists($this->userClass) || !is_subclass_of($this->userClass, UserModel::class)) {
            throw new Exception("Invalid 'userClass' in config!", 500);
        }

        $primaryValue = $this->session->get("user");
        if (isset($primaryValue)) {
            $user = new $this->userClass();
            $primaryKey = $user->primaryKey();
            $user = $this->userClass::findOne([$primaryKey => $primaryValue], $this->database);
            $this->user = $user ?: null;
        }

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

    public function login(DatabaseModel $user): void {
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set("user", $primaryValue);
    }

    public function logout(): void {
        $this->user = null;
        $this->session->remove("user");
    }

    public function isGuest(): bool {
        return empty($this->user);
    }

    public function getUsername(): string {
        return $this->isGuest() ? "Guest" : $this->user->displayName();
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