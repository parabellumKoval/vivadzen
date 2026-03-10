<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $databasePath = dirname(__DIR__) . '/database/testing.sqlite';

        if (!is_file($databasePath)) {
            touch($databasePath);
        }

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=' . $databasePath);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $databasePath;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $databasePath;

        $pdo = new PDO('sqlite:' . $databasePath);
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS ak_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                "key" VARCHAR(255) NOT NULL,
                "value" TEXT NULL,
                "cast" VARCHAR(64) NULL,
                "region" VARCHAR(32) NULL,
                "locale" VARCHAR(8) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )'
        );

        $app = require dirname(__DIR__) . '/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
