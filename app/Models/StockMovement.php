<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'business_id',
        'branch_id',
        'product_variation_id',
        'stock_batch_id',
        'user_id',
        'movement_type',
        'direction',
        'quantity',
        'reference_type',
        'reference_id',
        'reference_number',
        'notes',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'moved_at' => 'datetime',
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

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
