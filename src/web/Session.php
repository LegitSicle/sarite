<?php
namespace sarite\web;

class Session {
    protected const FLASH_KEY = "flash_values";

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $flashValues = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashValues as $key => &$flashValue) {
            $flashValue["remove"] = true;
        }

        $_SESSION[self::FLASH_KEY] = $flashValues;
    }

    public function __destruct() {
        $flashValues = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashValues as $key => &$flashValue) {
            if ($flashValue["remove"]) {
                unset($flashValues[$key]);
            }
        }

        $_SESSION[self::FLASH_KEY] = $flashValues;
    }

    public function setFlash(string $key, mixed $value): void {
        $_SESSION[self::FLASH_KEY][$key] = [
            "remove" => false,
            "value" => $value,
        ];
    }

    public function getFlash(string $key): mixed {
        return $_SESSION[self::FLASH_KEY][$key]["value"] ?? false;
    }

    public function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key): mixed {
        return $_SESSION[$key] ?? false;
    }

    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public function isPresent(string $key): bool {
        return isset($_SESSION[$key]);
    }
}