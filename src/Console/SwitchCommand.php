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

        if (!$css && !$frontend) {
            $this->error('Provide --css and/or --frontend');

            return self::FAILURE;
        }

        if ($css && !Frameworks::isValidCss($css)) {
            $this->error("Invalid CSS: $css");

            return self::FAILURE;
        }

        if ($frontend && !Frameworks::isValidFrontend($frontend)) {
            $this->error("Invalid frontend: $frontend");

            return self::FAILURE;
        }

        $written = true;

        if ($css) {
            $written = $this->setCssFramework($css) && $written;
            $this->info("Laravel Notifications CSS switched to: $css");
        }

        if ($frontend) {
            $written = $this->setFrontendFramework($frontend) && $written;
            $this->info("Laravel Notifications frontend switched to: $frontend");
        }

        if (!$written) {
            $this->warn('No writable .env file was found. The change applies to this process only.');
        }

        $this->clearCachedConfig();

        return self::SUCCESS;
    }
}
