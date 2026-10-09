<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Providers;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Jeremykenedy\LaravelNotifications\Console\InstallCommand;
use Jeremykenedy\LaravelNotifications\Console\SwitchCommand;
use Jeremykenedy\LaravelNotifications\Listeners\BroadcastNotificationCreated;
use Jeremykenedy\LaravelNotifications\Livewire\NotificationsList;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;
use Jeremykenedy\LaravelNotifications\Support\Frameworks;
use Jeremykenedy\LaravelNotifications\Support\Settings;
use Livewire\Livewire;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/notifications.php', 'notifications');
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(Settings::class);
    }

    public function boot(): void
    {
        if (config('notifications.broadcast.enabled', true)) {
            Event::listen(NotificationSent::class, BroadcastNotificationCreated::class);
        }

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Colours saved from the settings page are overlaid onto the config
        // here, so every view downstream just reads config('notifications.*').
        $this->app->make(Settings::class)->load();

        $this->registerCommands();
        $this->registerPublishing();

        // routes.enabled is the master switch it has always been, so turning it
        // off still takes the JSON endpoints with it.
        if (config('notifications.routes.enabled', true)) {
            // Settings go first: notifications/settings would otherwise be
            // swallowed by the notifications/{id} routes in web.php.
            if (config('notifications.settings.route_enabled', true)) {
                $this->loadRoutesFrom(__DIR__.'/../../routes/settings.php');
            }

            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

            if (config('notifications.api.enabled', true)) {
                $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
            }
        }

        $this->loadViewsFrom([$this->viewPath(), __DIR__.'/../../resources/views/'], 'notifications');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'notifications');

        if (class_exists(Livewire::class)) {
            Livewire::component('notifications-list', NotificationsList::class);
        }
    }

    /**
     * The view directory for the active CSS framework, falling back to the
     * package default when a framework ships no views of its own.
     */
    protected function viewPath(): string
    {
        $path = __DIR__.'/../../resources/views/'.Frameworks::css().'/blade';

        return is_dir($path)
            ? $path
            : __DIR__.'/../../resources/views/'.Frameworks::DEFAULT_CSS.'/blade';
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class, SwitchCommand::class]);
        }
    }

    protected function registerPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../../config/notifications.php' => config_path('notifications.php'),
        ], 'notifications-config');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/notifications'),
        ], 'notifications-views');

        $this->publishes([
            __DIR__.'/../../resources/lang' => lang_path('vendor/notifications'),
        ], 'notifications-lang');

        $frontend = Frameworks::frontend();

        if ($frontend !== 'blade' && $frontend !== 'livewire') {
            $this->publishes([
                __DIR__.'/../../resources/js/'.$frontend.'/pages' => resource_path('js/Pages/Notifications'),
            ], 'notifications-'.$frontend);
        }
    }
}
