<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Console\Concerns;

trait HandlesFrameworkSetup
{
    protected function setCssFramework(string $css): bool
    {
        config(['notifications.css_framework' => $css]);

        return $this->writeEnvValue('NOTIFICATIONS_CSS_FRAMEWORK', $css);
    }

    protected function setFrontendFramework(string $frontend): bool
    {
        config(['notifications.frontend' => $frontend]);

        return $this->writeEnvValue('NOTIFICATIONS_FRONTEND', $frontend);
    }

    /**
     * Write a key to the application .env file, replacing it when already present.
     * Returns false when there is no writable .env, which is the normal case for
     * a package test run.
     */
    protected function writeEnvValue(string $key, string $value): bool
    {
        $path = base_path('.env');

        if (!is_file($path) || !is_writable($path)) {
            return false;
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($path)) ?: [];
        $replaced = false;

        foreach ($lines as $index => $line) {
            if (str_starts_with($line, $key.'=')) {
                $lines[$index] = $key.'='.$value;
                $replaced = true;
            }
        }

        if (!$replaced) {
            $lines[] = $key.'='.$value;
        }

        file_put_contents($path, rtrim(implode(PHP_EOL, $lines), PHP_EOL).PHP_EOL);

        return true;
    }

    protected function clearCachedConfig(): void
    {
        if ($this->laravel->configurationIsCached()) {
            $this->callSilently('config:clear');
        }
    }
}
