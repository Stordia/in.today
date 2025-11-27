<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cuisine extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_en',
        'name_de',
        'name_el',
        'name_it',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cuisine $cuisine) {
            if (empty($cuisine->slug)) {
                $cuisine->slug = Str::slug($cuisine->name_en);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name_en');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the localized name for the given locale.
     */
    public function getName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $column = "name_{$locale}";

        return $this->{$column} ?? $this->name_en;
    }
}
