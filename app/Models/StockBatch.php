<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = [
        'business_id',
        'branch_id',
        'product_variation_id',
        'batch_number',
        'expiry_date',
        'received_quantity',
        'quantity',
        'received_at',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_quantity' => 'decimal:4',
            'quantity' => 'decimal:4',
            'received_at' => 'datetime',
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
        return $this->belongsTo(ProductVariation::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
