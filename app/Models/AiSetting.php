<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = [
        'enabled',
        'active_provider',
        'provider_settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'provider_settings' => 'array',
    ];
}
