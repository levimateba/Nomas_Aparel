<?php

namespace App\Support;

class PermissionCatalog
{
    /** @return array<string, array{group: string, label: string, description: string}> */
    public static function catalog(): array
    {
        return config('permissions.catalog', []);
    }

    /** @return list<string> */
    public static function names(): array
    {
        return array_keys(self::catalog());
    }

    /** @return array<string, array<string, array{group: string, label: string, description: string}>> */
    public static function grouped(): array
    {
        $groups = [];
        foreach (self::catalog() as $name => $meta) {
            $group = $meta['group'] ?? 'Other';
            $groups[$group][$name] = $meta;
        }

        return $groups;
    }

    public static function meta(string $name): ?array
    {
        return self::catalog()[$name] ?? null;
    }

    public static function label(string $name): string
    {
        return self::meta($name)['label'] ?? str_replace('_', ' ', ucfirst($name));
    }
}
