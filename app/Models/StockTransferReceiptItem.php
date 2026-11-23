<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferReceiptItem extends Model
{
    protected $fillable = [
        'business_id',
        'stock_transfer_receipt_id',
        'stock_transfer_item_id',
        'product_variation_id',
        'quantity_received',
        'quantity_damaged',
        'quantity_missing',
        'discrepancy_notes',
        'discrepancy_status',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_received' => 'decimal:4',
            'quantity_damaged' => 'decimal:4',
            'quantity_missing' => 'decimal:4',
            'resolved_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(
            StockTransferReceipt::class,
            'stock_transfer_receipt_id'
        );
    }

    public function transferItem(): BelongsTo
    {
        return $this->belongsTo(
            StockTransferItem::class,
            'stock_transfer_item_id'
        );
    }

    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
