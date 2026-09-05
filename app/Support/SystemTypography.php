<?php

namespace App\Support;

class SystemTypography
{
    /** @var array<string, string> */
    public const SIZES = [
        '12' => 'Small (12px)',
        '13' => 'Compact (13px)',
        '14' => 'Medium (14px)',
        '16' => 'Large (16px)',
        '18' => 'Extra Large (18px)',
        '20' => 'XX Large (20px)',
        '24' => 'Heading (24px)',
        '28' => 'Display (28px)',
        '32' => 'Maximum (32px)',
    ];

    public static function defaultSize(): string
    {
        return '14';
    }

    public static function isAllowedSize(string $size): bool
    {
        return array_key_exists($size, self::SIZES);
    }

    public static function cssFontSize(?string $size): string
    {
        $size = $size ?: self::defaultSize();

        return is_numeric($size) ? $size.'px' : self::defaultSize().'px';
    }
}
