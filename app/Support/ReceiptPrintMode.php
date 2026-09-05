<?php

namespace App\Support;

class ReceiptPrintMode
{
    public const CUSTOMER = 'customer';

    public const CUSTOMER_AND_BUSINESS = 'customer_and_business';

    public const BUSINESS = 'business';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::CUSTOMER => 'Customer Copy Only',
            self::CUSTOMER_AND_BUSINESS => 'Customer + Business Copy',
            self::BUSINESS => 'Business Copy Only',
        ];
    }

    public static function default(): string
    {
        return self::CUSTOMER;
    }

    public static function normalize(?string $mode): string
    {
        $mode = (string) $mode;

        return array_key_exists($mode, self::options()) ? $mode : self::default();
    }

    /**
     * @return list<string>
     */
    public static function copiesFor(?string $storeMode, ?string $copyOverride = null): array
    {
        $override = strtolower(trim((string) $copyOverride));
        if (in_array($override, ['customer', 'business'], true)) {
            return [$override];
        }

        return match (self::normalize($storeMode)) {
            self::BUSINESS => ['business'],
            self::CUSTOMER_AND_BUSINESS => ['customer', 'business'],
            default => ['customer'],
        };
    }
}
