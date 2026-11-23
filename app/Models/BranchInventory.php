<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchInventory extends Model
{
    protected $fillable = [
        'business_id',
        'branch_id',
        'product_variation_id',
        'quantity',
        'minimum_stock',
        'reorder_level',
        'reorder_quantity',
        'last_stocked_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'minimum_stock' => 'decimal:4',
            'reorder_level' => 'decimal:4',
            'reorder_quantity' => 'decimal:4',
            'last_stocked_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class, 'product_variation_id', 'product_variation_id');
    }
}
