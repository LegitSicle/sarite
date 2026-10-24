<?php
namespace sarite;

require_once dirname(__DIR__) . "/vendor/autoload.php";
use Dotenv\Dotenv;
Dotenv::createImmutable(dirname(__DIR__))->load();

function safe_encode(mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE);
}

function view_name(string $path): string {
    return ltrim($path, '/');
}