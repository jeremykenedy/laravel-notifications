<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Support;

/**
 * The colours a notification is drawn with, and the tints derived from them.
 *
 * Views cannot use a user supplied colour as a Tailwind or Bootstrap class, so
 * every colour is emitted as a CSS custom property and the tints are computed
 * here rather than in CSS. That keeps the output identical on all three CSS
 * frameworks and avoids depending on color-mix().
 */
final class Colors
{
    /** Notification types that carry a colour of their own. */
    public const TYPES = ['info', 'success', 'warning', 'danger', 'system'];

    /** Colour keys that are not tied to a notification type. */
    public const ACCENTS = ['unread', 'read', 'badge'];

    /** Alpha applied to a type colour when it is used as an icon background. */
    public const TINT_ALPHA = 0.14;

    /** Alpha applied to a type colour when it is used as a row background. */
    public const ROW_ALPHA = 0.07;

    /** Alpha applied to a type colour when it is used as a border. */
    public const BORDER_ALPHA = 0.35;

    /**
     * Every configurable colour key, in the order the settings form shows them.
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_merge(self::TYPES, self::ACCENTS);
    }

    public static function isValid(?string $color): bool
    {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
    }

    /**
     * The colour configured for a key, falling back to the shipped default and
     * then to a neutral grey so a malformed value can never blank the UI.
     */
    public static function get(string $key): string
    {
        $color = config('notifications.colors.'.$key);

        if (self::isValid($color)) {
            return strtolower($color);
        }

        $default = config('notifications.settings.defaults.colors.'.$key);

        return self::isValid($default) ? strtolower($default) : '#6b7280';
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $colors = [];

        foreach (self::keys() as $key) {
            $colors[$key] = self::get($key);
        }

        return $colors;
    }

    /**
     * An rgba() string for a colour at the given alpha.
     */
    public static function rgba(string $color, float $alpha): string
    {
        [$r, $g, $b] = self::rgb($color);

        return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim(rtrim(number_format($alpha, 3, '.', ''), '0'), '.'));
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function rgb(string $color): array
    {
        $hex = ltrim(self::isValid($color) ? $color : '#6b7280', '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Black or white, whichever stays readable on the given colour. Uses the
     * WCAG relative luminance formula so a light pick does not produce white
     * text on a pale background.
     */
    public static function readableOn(string $color): string
    {
        [$r, $g, $b] = self::rgb($color);

        $channel = static function (int $value): float {
            $v = $value / 255;

            return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        };

        $luminance = 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);

        return $luminance > 0.179 ? '#111827' : '#ffffff';
    }

    /**
     * The CSS custom properties every view reads, as declaration text.
     */
    public static function cssVariables(): string
    {
        $lines = [];

        foreach (self::all() as $key => $color) {
            $lines[] = sprintf('--notifications-%s: %s;', $key, $color);
            $lines[] = sprintf('--notifications-%s-tint: %s;', $key, self::rgba($color, self::TINT_ALPHA));
            $lines[] = sprintf('--notifications-%s-row: %s;', $key, self::rgba($color, self::ROW_ALPHA));
            $lines[] = sprintf('--notifications-%s-border: %s;', $key, self::rgba($color, self::BORDER_ALPHA));
            $lines[] = sprintf('--notifications-%s-on: %s;', $key, self::readableOn($color));
        }

        return implode("\n    ", $lines);
    }
}
