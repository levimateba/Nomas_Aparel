<?php

namespace App\Support;

class PublicStorageUrl
{
    public static function fromPath(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (
            str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, '/storage/')
        ) {
            return $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return '/'.ltrim($value, '/');
        }

        if (str_starts_with($value, 'public/')) {
            $value = substr($value, 7);
        }

        return '/storage/'.ltrim($value, '/');
    }
}