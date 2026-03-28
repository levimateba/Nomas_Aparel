<?php

namespace App\Models;

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
}
