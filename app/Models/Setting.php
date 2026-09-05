<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use App\Support\ReceiptPrintMode;
use App\Support\SystemTypography;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'site_name',
        'trading_name',
        'business_registration_number',
        'tax_pin',
        'phone',
        'email',
        'city',
        'currency',
        'address',
        'receipt_header',
        'receipt_footer',
        'receipt_print_mode',
        'auto_open_drawer_cash',
        'auto_print_receipt',
        'escpos_enabled',
        'logo_show_on_login',
        'logo_show_on_sidebar',
        'logo_show_on_receipts',
        'tax_enabled',
        'tax_rate',
        'tax_inclusive',
        'require_open_shift',
        'enforce_credit_limit',
        'allow_negative_stock',
        'pos_location_id',
        'pos_sell_from_all_locations',
        'online_sales_location_id',
        'online_sales_stock_mode',
        'online_fulfilment_strategy',
        'system_font_size',
        'audit_trail_enabled',
        'audit_trail_disabled_by_name',
        'audit_trail_disabled_at',
        'site_tagline',
        'logo',
        'favicon',
        'footer_text',
        'primary_color',
        'secondary_color',
        'tertiary_color',
    ];

    protected $casts = [
        'auto_open_drawer_cash' => 'boolean',
        'auto_print_receipt' => 'boolean',
        'escpos_enabled' => 'boolean',
        'logo_show_on_login' => 'boolean',
        'logo_show_on_sidebar' => 'boolean',
        'logo_show_on_receipts' => 'boolean',
        'tax_enabled' => 'boolean',
        'tax_rate' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'require_open_shift' => 'boolean',
        'enforce_credit_limit' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'pos_sell_from_all_locations' => 'boolean',
        'audit_trail_enabled' => 'boolean',
        'audit_trail_disabled_at' => 'datetime',
    ];

    public static function get_settings()
    {
        if (! Schema::hasTable('settings')) {
            return new self([
                'site_name' => 'Nomas Apparel',
                'trading_name' => 'Nomas Apparel',
                'site_tagline' => 'Suits, uniforms, bags and shoes',
                'currency' => 'KES',
                'receipt_footer' => 'Thank you for shopping with us.',
                'receipt_print_mode' => ReceiptPrintMode::default(),
                'system_font_size' => SystemTypography::defaultSize(),
            ]);
        }

        return self::firstOrCreate([], [
            'site_name' => 'Nomas Apparel',
            'trading_name' => 'Nomas Apparel',
            'site_tagline' => 'Suits, uniforms, bags and shoes',
            'currency' => 'KES',
            'receipt_footer' => 'Thank you for shopping with us. Goods once sold are not returnable without receipt.',
            'receipt_print_mode' => ReceiptPrintMode::default(),
            'system_font_size' => SystemTypography::defaultSize(),
            'tax_rate' => 16,
            'tax_inclusive' => true,
            'auto_open_drawer_cash' => true,
            'escpos_enabled' => true,
        ]);
    }

    public function getLogoAttribute($value)
    {
        return PublicStorageUrl::fromPath($value);
    }

    public function getFaviconAttribute($value)
    {
        return PublicStorageUrl::fromPath($value);
    }

    public function displayName(): string
    {
        return trim((string) ($this->trading_name ?: $this->site_name ?: 'Store'));
    }

    public function receiptPrintMode(): string
    {
        return ReceiptPrintMode::normalize($this->receipt_print_mode ?? null);
    }

    public function logoStoragePath(): ?string
    {
        $path = method_exists($this, 'getRawOriginal')
            ? $this->getRawOriginal('logo')
            : null;
        $path = trim((string) ($path ?: ''));

        return $path !== '' ? $path : null;
    }

    public function hasLogoFile(): bool
    {
        $path = $this->logoStoragePath();
        if (! $path) {
            return false;
        }

        try {
            return Storage::disk('public')->exists($path);
        } catch (\Throwable) {
            return false;
        }
    }

    public function showsLogoOnLogin(): bool
    {
        return (bool) $this->logo_show_on_login && $this->hasLogoFile();
    }

    public function showsLogoOnSidebar(): bool
    {
        return (bool) $this->logo_show_on_sidebar && $this->hasLogoFile();
    }

    public function showsLogoOnReceipts(): bool
    {
        return (bool) $this->logo_show_on_receipts && $this->hasLogoFile();
    }

    /**
     * Absolute filesystem path to the uploaded logo (for DomPDF / local image embedding).
     */
    public function logoAbsolutePath(): ?string
    {
        $path = $this->logoStoragePath();
        if (! $path || ! $this->hasLogoFile()) {
            return null;
        }

        try {
            return Storage::disk('public')->path($path);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Data-URI for embedding the logo in PDFs and print views without depending on HTTP.
     */
    public function logoDataUri(): ?string
    {
        $absolute = $this->logoAbsolutePath();
        if (! $absolute || ! is_readable($absolute)) {
            return null;
        }

        $binary = @file_get_contents($absolute);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($absolute) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    public function shouldOpenDrawerAfterCashSale(string $paymentMethod, float $changeAmount = 0): bool
    {
        if ($changeAmount > 0) {
            return true;
        }

        if (strtolower($paymentMethod) !== 'cash') {
            return false;
        }

        return (bool) ($this->auto_open_drawer_cash ?? true);
    }

    public function effectiveTaxRate(float|int|string|null $productRate = null): float
    {
        if (! $this->tax_enabled) {
            return 0.0;
        }

        $productRate = (float) $productRate;

        return $productRate > 0 ? $productRate : (float) ($this->tax_rate ?? 0);
    }

    public function taxFromAmount(float $amount, float|int|string|null $productRate = null): float
    {
        $rate = $this->effectiveTaxRate($productRate);
        if ($rate <= 0 || $amount <= 0) {
            return 0.0;
        }

        if ($this->tax_inclusive ?? true) {
            return round($amount * $rate / (100 + $rate), 2);
        }

        return round($amount * ($rate / 100), 2);
    }

    public function totalWithTax(float $amountAfterDiscount, float $tax): float
    {
        if ($this->tax_inclusive ?? true) {
            return round($amountAfterDiscount, 2);
        }

        return round($amountAfterDiscount + $tax, 2);
    }

    public function taxReceiptLabel(): string
    {
        $rate = (float) ($this->tax_rate ?? 0);
        $formatted = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
        $label = $rate > 0 ? 'VAT '.$formatted.'%' : 'Tax';

        if ($this->tax_inclusive ?? true) {
            $label .= ' (incl.)';
        }

        return $label;
    }

    public function uiFontSizeCss(): string
    {
        return SystemTypography::cssFontSize($this->system_font_size ?? null);
    }
}
