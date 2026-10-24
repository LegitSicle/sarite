<?php
namespace sarite\web;

use function strlen;

/**
 * Main handler for super globals such as `$_SERVER`, `$_GET`, and `$_POST`.
 */
class Request {
    /**
     * Used for routing, it returns the `$_SERVER["REQUEST_URI"]` that has the starting `'/website'` removed and anything proceeding '`?'`.
     * @return string The path route.
     */
    public function path(): string {
        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?? '/';
        $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        return $uri === '' ? '/' : $uri;
    }

    public function method(): string {
        return strtolower($_SERVER["REQUEST_METHOD"]);
    }

    public function isGet(): bool {
        return $this->method() === "get";
    }

    public function isPost(): bool {
        return $this->method() === "post";
    }

    /**
     * Gives the request body. For example for `GET`, `http://localhost/website/home?username=Sicle` link will have a body array of <br>
     * `["username" => "Sicle"]`.
     * The same for `POST`.
     * @return array A filtered array from `$_GET` or `$_POST`.
     */
    public function body(): array {
        $body = [];
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        } else if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }           
        }
        return $body;
    }
}