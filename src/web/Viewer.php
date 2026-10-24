<?php
namespace sarite\web;
use Exception;

class Viewer {
    private static string $viewPath;
    private static string $layoutPath;

    public static function viewExists(string $name): bool {
        return file_exists(static::$viewPath . "{$name}.php");
    }

    public static function getContents(string $view, string $layout, array $params = []): string {
        return str_replace("{{content}}", static::getView($view, $params), static::getLayout($layout, $params));
    }

    public static function getView(string $name, array $params = []): string {
        $filePath = static::$viewPath . "{$name}.php";
        return static::getRender($filePath, $params);
    }

    public static function getLayout(string $name, array $params = []): string {
        $filePath = static::$layoutPath . "{$name}.php";
        return static::getRender($filePath, $params);
    }

    public static function setPaths(string $viewPath, ?string $layoutPath = null): void {
        static::$viewPath = $viewPath;
        static::$layoutPath = $layoutPath ?? "{$viewPath}layouts" . DIRECTORY_SEPARATOR;
    }

    private static function getRender(string $filePath, array $params = []): string {
        if (!file_exists($filePath)) throw new Exception("{$filePath} file does not exists", 404);
        if (!empty($params)) extract($params);
        ob_start();
        require_once $filePath;
        return ob_get_clean();
    }
}