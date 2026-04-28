<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $this->assertSafeTestingDatabaseConfiguration($app);

        return $app;
    }

    protected function assertSafeTestingDatabaseConfiguration(Application $app): void
    {
        $environment = (string) $app->environment();
        $defaultConnection = (string) $app['config']->get('database.default');
        $databaseName = (string) $app['config']->get("database.connections.{$defaultConnection}.database");

        if ($app->configurationIsCached()) {
            throw new RuntimeException(
                'Refusing to run tests with cached configuration. Run "php artisan config:clear" before running the test suite.'
            );
        }

        if ($environment !== 'testing' || $defaultConnection !== 'sqlite' || $databaseName !== ':memory:') {
            throw new RuntimeException(sprintf(
                'Refusing to run tests against an unsafe database target. Expected APP_ENV=testing with sqlite/:memory:, got APP_ENV=%s, DB_CONNECTION=%s, DB_DATABASE=%s.',
                $environment,
                $defaultConnection,
                $databaseName !== '' ? $databaseName : '(empty)'
            ));
        }
    }
}
