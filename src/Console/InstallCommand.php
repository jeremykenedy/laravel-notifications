<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Console;

use Illuminate\Console\Command;
use Jeremykenedy\LaravelNotifications\Console\Concerns\HandlesFrameworkSetup;
use Jeremykenedy\LaravelNotifications\Support\Frameworks;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class InstallCommand extends Command
{
    use HandlesFrameworkSetup;

    protected $signature = 'notifications:install
        {--css= : CSS framework (tailwind, bootstrap5, bootstrap4)}
        {--frontend= : Frontend framework (blade, livewire, vue, react, svelte)}';

    protected $description = 'Install and configure the Laravel notifications package';

    public function handle(): int
    {
        $css = $this->option('css') ?? select(
            label: 'CSS Framework',
            options: ['tailwind' => 'Tailwind v4', 'bootstrap5' => 'Bootstrap 5', 'bootstrap4' => 'Bootstrap 4'],
            default: Frameworks::DEFAULT_CSS,
        );

        $frontend = $this->option('frontend') ?? select(
            label: 'Frontend Framework',
            options: ['blade' => 'Blade + Alpine.js', 'livewire' => 'Livewire', 'vue' => 'Vue 3', 'react' => 'React', 'svelte' => 'Svelte'],
            default: Frameworks::DEFAULT_FRONTEND,
        );

        if (!Frameworks::isValidCss($css)) {
            $this->error("Invalid CSS framework: {$css}");

            return self::FAILURE;
        }

        if (!Frameworks::isValidFrontend($frontend)) {
            $this->error("Invalid frontend framework: {$frontend}");

            return self::FAILURE;
        }

        info("Installing notifications with {$css} + {$frontend}...");

        $this->call('vendor:publish', [
            '--tag'   => 'notifications-config',
            '--force' => true,
        ]);

        $cssWritten = $this->setCssFramework($css);
        $frontendWritten = $this->setFrontendFramework($frontend);

        if (!$cssWritten || !$frontendWritten) {
            $this->warn('No writable .env file was found. Set NOTIFICATIONS_CSS_FRAMEWORK and NOTIFICATIONS_FRONTEND by hand.');
        }

        $this->clearCachedConfig();

        info('notifications installed successfully.');
        info('Run: php artisan migrate && npm run build');

        return self::SUCCESS;
    }
}
