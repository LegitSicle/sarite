<?php
namespace sarite\web;
use sarite\controller\Middleware;
use sarite\controller\Controller;
use Exception;
use sarite\controller\RedirectController;

class Route {
    public readonly string $method;
    public readonly string $url;
    public readonly string $destination;
    public readonly string $controllerClass;
    public readonly array $data;
    /**
     * @var Middleware[]
     */
    public readonly array $middlewares;

    public function get(string $url, string $controllerClass): self {
        if (!is_subclass_of($controllerClass, Controller::class)) throw new Exception("{$controllerClass} is not a valid Controller", 500);
        $this->method = "get";
        $this->url = $url;
        $this->controllerClass = $controllerClass;
        return $this;
    }

    public function post(string $url, string $controllerClass): self {
        if (!is_subclass_of($controllerClass, Controller::class)) throw new Exception("{$controllerClass} is not a valid Controller", 500);
        $this->method = "post";
        $this->url = $url;
        $this->controllerClass = $controllerClass;
        return $this;
    }

    public function redirect(string $url, string $destination): self {
        $this->method = "get";
        $this->url = $url;
        $this->controllerClass = RedirectController::class;
        $this->data = ["destination" => $destination];
        return $this;
    }

    public function injectData(array $data): self {
        $this->data = $data;
        return $this;
    }

    public function middlewares(Middleware ...$middlewares): self {
        $this->middlewares = $middlewares;
        return $this;
    }
}