<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransferItem extends Model
{
    protected $fillable = [
        'business_id',
        'stock_transfer_id',
        'product_variation_id',
        'quantity_requested',
        'quantity_dispatched',
        'quantity_received',
        'quantity_damaged',
        'quantity_missing',
        'discrepancy_notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:4',
            'quantity_dispatched' => 'decimal:4',
            'quantity_received' => 'decimal:4',
            'quantity_damaged' => 'decimal:4',
            'quantity_missing' => 'decimal:4',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(StockTransferReceiptItem::class);
    }
}
