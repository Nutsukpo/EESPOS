<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'product_id',
        'name',
        'sku',
        'attributes',
        'cost_price',
        'selling_price',
        'tax_status',
        'tax_rate',
        'tax_inclusive',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'cost_price' => 'decimal:4',
            'selling_price' => 'decimal:4',
            'tax_rate' => 'decimal:4',
            'tax_inclusive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }
}
