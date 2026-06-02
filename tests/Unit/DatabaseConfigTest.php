<?php

namespace Tests\Unit;

use Tests\TestCase;

class DatabaseConfigTest extends TestCase
{
    public function test_mysql_is_the_default_database_connection_when_env_is_missing(): void
    {
        $originalEnvironment = [
            'DB_CONNECTION' => getenv('DB_CONNECTION') ?: false,
            'DB_DATABASE' => getenv('DB_DATABASE') ?: false,
        ];

        putenv('DB_CONNECTION');
        putenv('DB_DATABASE');
        unset($_ENV['DB_CONNECTION'], $_SERVER['DB_CONNECTION'], $_ENV['DB_DATABASE'], $_SERVER['DB_DATABASE']);

        $config = require __DIR__.'/../../config/database.php';

        $this->assertSame('mysql', $config['default']);

        foreach ($originalEnvironment as $key => $value) {
            if ($value === false) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);

                continue;
            }

            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
