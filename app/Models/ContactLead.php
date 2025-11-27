<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactLead extends Model
{
    protected $table = 'contact_leads';

    protected $fillable = [
        'locale',
        'name',
        'email',
        'phone',
        'restaurant_name',
        'city',
        'country',
        'website_url',
        'type',
        'services',
        'budget',
        'message',
        'source_url',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'services' => 'array',
    ];
}
