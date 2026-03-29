<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Tests;

use Jeremykenedy\LaravelNotifications\Providers\NotificationServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [NotificationServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('notifications.enabled', true);
        $app['config']->set('notifications.routes.enabled', false);
    }
}
