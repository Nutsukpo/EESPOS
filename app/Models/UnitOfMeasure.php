<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'abbreviation',
        'unit_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outgoingConversions(): HasMany
    {
        return $this->hasMany(
            ProductUnitConversion::class,
            'from_unit_id'
        );
    }

    public function incomingConversions(): HasMany
    {
        return $this->hasMany(
            ProductUnitConversion::class,
            'to_unit_id'
        );
    }
}
