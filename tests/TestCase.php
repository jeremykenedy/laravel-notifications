<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Jeremykenedy\LaravelNotifications\Providers\NotificationServiceProvider;
use Jeremykenedy\LaravelNotifications\Support\Frameworks;
use Jeremykenedy\LaravelNotifications\Tests\Fixtures\Role;
use Jeremykenedy\LaravelNotifications\Tests\Fixtures\User;
use Jeremykenedy\LaravelNotifications\Tests\Fixtures\UuidUser;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;

abstract class TestCase extends OrchestraTestCase
{
    protected string $cssFramework = Frameworks::DEFAULT_CSS;

    /** @var array<string, mixed> */
    protected array $configOverrides = [];

    protected function getPackageProviders($app): array
    {
        return [NotificationServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        foreach (['mysql', 'mariadb', 'pgsql', 'sqlsrv'] as $connection) {
            $app['config']->set('database.connections.'.$connection, null);
        }

        $app['config']->set('notifications.css_framework', $this->cssFramework);
        $app['config']->set('notifications.user_model', User::class);
        $app['config']->set('notifications.role_model', Role::class);
        $app['config']->set('notifications.send.middleware', ['web', 'auth']);
        $app['config']->set('notifications.broadcast.enabled', false);

        // Sanctum is not a dependency of this package, so the suite guards the
        // JSON routes with the session guard instead.
        $app['config']->set('notifications.api.middleware', ['api', 'auth']);

        foreach ($this->configOverrides as $key => $value) {
            $app['config']->set($key, $value);
        }

        $app['config']->set('view.paths', array_merge(
            [__DIR__.'/Fixtures/views'],
            $app['config']->get('view.paths', []),
        ));
    }

    /**
     * Applications are expected to provide a login route for the auth
     * middleware to redirect guests to.
     */
    protected function defineRoutes($router): void
    {
        $router->get('/login', fn () => 'login')->name('login');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstRealDatabase();
        $this->createSchema();
    }

    /**
     * The suite is only ever allowed to touch an in memory SQLite database.
     */
    protected function guardAgainstRealDatabase(): void
    {
        $connection = config('database.default');
        $driver = config('database.connections.'.$connection.'.driver');
        $database = config('database.connections.'.$connection.'.database');

        if ($driver !== 'sqlite' || $database !== ':memory:') {
            $this->fail("Tests must run against sqlite :memory:, got [{$driver}] [{$database}].");
        }
    }

    /**
     * Rebuild the application against a different CSS framework. The in memory
     * database goes with the old container, so the schema is created again.
     */
    protected function usingCssFramework(string $css): static
    {
        $this->cssFramework = $css;

        return $this->rebuildApplication();
    }

    /**
     * Rebuild the application with configuration that has to be in place before
     * the service provider boots, such as whether routes are registered at all.
     *
     * @param array<string, mixed> $overrides
     */
    protected function usingConfig(array $overrides): static
    {
        $this->configOverrides = array_merge($this->configOverrides, $overrides);

        return $this->rebuildApplication();
    }

    protected function rebuildApplication(): static
    {
        $this->refreshApplication();
        $this->createSchema();

        return $this;
    }

    protected function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::create('uuid_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id');
            $table->foreignId('user_id');
        });

        $this->createNotificationsTable();

        $this->packageMigration()->up();
    }

    /**
     * The table Laravel's own notifications migration creates. The package adds
     * a column to it, so the tests have to stand it up first.
     */
    protected function createNotificationsTable(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    protected function packageMigration(): object
    {
        return require __DIR__.'/../database/migrations/2026_03_28_000001_add_archived_at_to_notifications_table.php';
    }

    protected function makeUser(string $email = 'user@example.test'): User
    {
        return User::create(['name' => 'Test User', 'email' => $email]);
    }

    protected function makeUuidUser(string $email = 'uuid@example.test'): UuidUser
    {
        return UuidUser::create(['name' => 'Uuid User', 'email' => $email]);
    }

    protected function makeRole(string $slug = 'admin'): Role
    {
        return Role::create(['name' => ucfirst($slug), 'slug' => $slug]);
    }

    /**
     * Put a database notification on the user and hand back the stored row.
     */
    protected function notify(Authenticatable $user, string $title = 'Title', string $message = 'Message', string $type = 'info'): object
    {
        $existing = $user->notifications()->pluck('id')->all();

        $this->service()->send($user, $title, $message, $type);

        return $user->notifications()->whereNotIn('id', $existing)->firstOrFail();
    }

    protected function service(): NotificationService
    {
        return $this->app->make(NotificationService::class);
    }
}
