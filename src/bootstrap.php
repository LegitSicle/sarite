<?php
namespace sarite;
use Dotenv\Dotenv;
use Composer\InstalledVersions;

$projectRoot = InstalledVersions::getRootPackage()['install_path'];
Dotenv::createImmutable($projectRoot)->load();

function safe_encode(mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE);
}

function view_name(string $path): string {
    return ltrim($path, '/');
}