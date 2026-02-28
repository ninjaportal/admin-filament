<?php

use Dotenv\Dotenv;

$root = dirname(__DIR__, 3);

require $root.'/vendor/autoload.php';

if (file_exists($root.'/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

if (! extension_loaded('pdo_sqlite')) {
    $database = 'ninjaportal_admin_filament_test';

    $_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'mysql';
    $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = $database;

    $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '3306';
    $username = $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';

    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec(sprintf('DROP DATABASE IF EXISTS `%s`', $database));
    $pdo->exec(sprintf('CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $database));
}

require_once __DIR__.'/TestCase.php';
