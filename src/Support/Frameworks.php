<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Support;

/**
 * The CSS and frontend frameworks this package ships views for.
 */
final class Frameworks
{
    public const CSS = ['tailwind', 'bootstrap5', 'bootstrap4'];

    public const FRONTEND = ['blade', 'livewire', 'vue', 'react', 'svelte'];

    public const DEFAULT_CSS = 'tailwind';

    public const DEFAULT_FRONTEND = 'blade';

    public static function isValidCss(string $css): bool
    {
        return in_array($css, self::CSS, true);
    }

    public static function isValidFrontend(string $frontend): bool
    {
        return in_array($frontend, self::FRONTEND, true);
    }

    /**
     * The active CSS framework, falling back to the laravel-ui-kit setting
     * when this package has no preference of its own.
     */
    public static function css(): string
    {
        $css = config('notifications.css_framework')
            ?: config('ui-kit.css_framework', self::DEFAULT_CSS);

        return self::isValidCss((string) $css) ? (string) $css : self::DEFAULT_CSS;
    }

    public static function frontend(): string
    {
        $frontend = config('notifications.frontend')
            ?: config('ui-kit.frontend', self::DEFAULT_FRONTEND);

        return self::isValidFrontend((string) $frontend) ? (string) $frontend : self::DEFAULT_FRONTEND;
    }
}
