<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'site_name',
        'site_tagline',
        'logo',
        'favicon',
        'footer_text',
        'primary_color',
        'secondary_color',
        'tertiary_color',
    ];

    /**
     * Get the first (and only) settings record
     */
    public static function get_settings()
    {
        if (! Schema::hasTable('settings')) {
            return new self([
                'site_name' => 'Elgon Tech',
                'site_tagline' => 'ICT Consultancy',
            ]);
        }

        return self::firstOrCreate([], [
            'site_name' => 'Elgon Tech',
            'site_tagline' => 'ICT Consultancy',
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
}
