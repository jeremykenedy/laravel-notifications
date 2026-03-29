<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Providers;

use Illuminate\Support\ServiceProvider;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;
use Livewire\Livewire;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/notifications.php', 'notifications');
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
    {
        // Listen for all notifications and broadcast via WebSocket
        if (config('notifications.broadcast.enabled', true)) {
            \Illuminate\Support\Facades\Event::listen(
                \Illuminate\Notifications\Events\NotificationSent::class,
                \Jeremykenedy\LaravelNotifications\Listeners\BroadcastNotificationCreated::class,
            );
        }

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->registerCommands();
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../../config/notifications.php' => config_path('notifications.php')], 'notifications-config');
            $this->publishes([__DIR__ . '/../../resources/views' => resource_path('views/vendor/notifications')], 'notifications-views');

            $frontend = config('ui-kit.frontend', 'blade');
            if ($frontend !== 'blade') {
                $this->publishes([
                    __DIR__ . '/../../resources/js/' . $frontend . '/pages' => resource_path('js/Pages/Notifications'),
                ], 'notifications-' . $frontend);
            }
        }

        if (config('notifications.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }

        $css = config('ui-kit.css_framework', 'tailwind');
        $path = __DIR__ . '/../../resources/views/' . $css . '/blade';
        if (! is_dir($path)) {
            $path = __DIR__ . '/../../resources/views/tailwind/blade';
        }
        $this->loadViewsFrom([$path, __DIR__ . '/../../resources/views/'], 'notifications');

        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'notifications');
        if (class_exists(\Livewire\Livewire::class)) {
            Livewire::component('notifications-list', \Jeremykenedy\LaravelNotifications\Livewire\NotificationsList::class);
        }

    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([\Jeremykenedy\LaravelNotifications\Console\SwitchCommand::class, 
                \Jeremykenedy\LaravelNotifications\Console\InstallCommand::class,
            ]);
        }
    }
}
