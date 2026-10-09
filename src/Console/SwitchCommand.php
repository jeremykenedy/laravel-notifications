<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Console;

use Illuminate\Console\Command;
use Jeremykenedy\LaravelNotifications\Console\Concerns\HandlesFrameworkSetup;
use Jeremykenedy\LaravelNotifications\Support\Frameworks;

class SwitchCommand extends Command
{
    use HandlesFrameworkSetup;

    protected $signature = 'notifications:switch
        {--css= : CSS framework (tailwind, bootstrap5, bootstrap4)}
        {--frontend= : Frontend framework (blade, livewire, vue, react, svelte)}';

    protected $description = 'Switch the CSS and/or frontend framework for Laravel Notifications';

    public function handle(): int
    {
        $css = $this->option('css');
        $frontend = $this->option('frontend');
        $problem = $this->problemWith($css, $frontend);

        if ($problem !== null) {
            $this->error($problem);

            return self::FAILURE;
        }

        $written = $this->switchTo($css, 'CSS', fn (string $value) => $this->setCssFramework($value));
        $written = $this->switchTo($frontend, 'frontend', fn (string $value) => $this->setFrontendFramework($value))
            && $written;

        if (!$written) {
            $this->warn('No writable .env file was found. The change applies to this process only.');
        }

        $this->clearCachedConfig();

        return self::SUCCESS;
    }

    protected function problemWith(?string $css, ?string $frontend): ?string
    {
        if (!$css && !$frontend) {
            return 'Provide --css and/or --frontend';
        }

        if ($css && !Frameworks::isValidCss($css)) {
            return "Invalid CSS: $css";
        }

        if ($frontend && !Frameworks::isValidFrontend($frontend)) {
            return "Invalid frontend: $frontend";
        }

        return null;
    }

    /**
     * Apply one option and report it. Returns false only when the value could
     * not be written to .env, and true when there was nothing to switch.
     */
    protected function switchTo(?string $value, string $label, callable $apply): bool
    {
        if (!$value) {
            return true;
        }

        $written = $apply($value);
        $this->info("Laravel Notifications {$label} switched to: $value");

        return $written;
    }
}
