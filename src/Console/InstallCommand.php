<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Console;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class InstallCommand extends Command
{
    protected $signature = 'notifications:install
        {--css= : CSS framework (tailwind, bootstrap5, bootstrap4)}
        {--frontend= : Frontend framework (blade, livewire, vue, react, svelte)}';

    protected $description = 'Install and configure the Laravel notifications package';

    public function handle(): int
    {
        $css = $this->option('css') ?? select(
            label: 'CSS Framework',
            options: ['tailwind' => 'Tailwind v4', 'bootstrap5' => 'Bootstrap 5', 'bootstrap4' => 'Bootstrap 4'],
            default: 'tailwind',
        );

        $frontend = $this->option('frontend') ?? select(
            label: 'Frontend Framework',
            options: ['blade' => 'Blade + Alpine.js', 'livewire' => 'Livewire', 'vue' => 'Vue 3', 'react' => 'React', 'svelte' => 'Svelte'],
            default: 'blade',
        );

        info("Installing notifications with {$css} + {$frontend}...");

        $this->call('vendor:publish', [
            '--tag'   => 'notifications-config',
            '--force' => true,
        ]);

        $this->updateEnv('UI_KIT_CSS', $css);
        $this->updateEnv('UI_KIT_FRONTEND', $frontend);

        info('notifications installed successfully.');
        info('Run: php artisan migrate && npm run build');

        return self::SUCCESS;
    }

    protected function updateEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        if (str_contains($content, "{$key}=")) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}";
        }

        file_put_contents($path, $content);
    }
}
