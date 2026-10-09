<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Support;

use Jeremykenedy\LaravelNotifications\Models\NotificationSetting;

/**
 * Settings edited in the UI are stored as one JSON row and overlaid onto the
 * package config at boot, so every view keeps reading config('notifications.*')
 * and never has to know whether a value came from the file or the database.
 */
class Settings
{
    public const KEY = 'colors';

    public function model(): NotificationSetting
    {
        $model = new NotificationSetting();

        $connection = config('notifications.settings.connection');

        return $connection === null ? $model : $model->setConnection($connection);
    }

    /**
     * False before the migration has run, which is the normal state on a fresh
     * install, so loading has to be a no-op rather than an error.
     */
    public function available(): bool
    {
        $model = $this->model();

        try {
            return $model->getConnection()->getSchemaBuilder()->hasTable($model->getTable());
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Snapshot the shipped defaults, then overlay anything stored. The snapshot
     * is what the reset control on each field restores to.
     */
    public function load(): void
    {
        $this->rememberDefaults();

        if (!config('notifications.settings.enabled', true) || !$this->available()) {
            return;
        }

        $this->apply($this->stored());
    }

    public function rememberDefaults(): void
    {
        if (config('notifications.settings.defaults') !== null) {
            return;
        }

        config(['notifications.settings.defaults' => ['colors' => config('notifications.colors', [])]]);
    }

    /**
     * @return array<string, string>
     */
    public function stored(): array
    {
        if (!$this->available()) {
            return [];
        }

        $record = $this->model()->newQuery()->find(self::KEY);
        $value = $record?->value;

        return is_array($value) ? $value : [];
    }

    /**
     * @param array<string, mixed> $colors
     */
    public function apply(array $colors): void
    {
        foreach ($this->valid($colors) as $key => $color) {
            config(['notifications.colors.'.$key => $color]);
        }
    }

    /**
     * @param array<string, mixed> $colors
     */
    public function save(array $colors): void
    {
        $clean = $this->valid($colors);

        $this->model()->newQuery()->updateOrCreate(['key' => self::KEY], ['value' => $clean]);

        $this->apply($clean);
    }

    /**
     * Only configurable keys with a six digit hex value survive, lowercased.
     *
     * @param array<string, mixed> $colors
     * @return array<string, string>
     */
    protected function valid(array $colors): array
    {
        $valid = [];

        foreach (Colors::keys() as $key) {
            if (isset($colors[$key]) && Colors::isValid($colors[$key])) {
                $valid[$key] = strtolower($colors[$key]);
            }
        }

        return $valid;
    }

    /**
     * Drop every stored colour so the shipped defaults take over again.
     */
    public function reset(): void
    {
        $this->model()->newQuery()->where('key', self::KEY)->delete();

        $defaults = config('notifications.settings.defaults.colors', []);

        foreach ($defaults as $key => $color) {
            config(['notifications.colors.'.$key => $color]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return config('notifications.settings.defaults.colors', []);
    }
}
