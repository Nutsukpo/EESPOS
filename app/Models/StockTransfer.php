<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = [
        'business_id',
        'from_branch_id',
        'to_branch_id',
        'reference_number',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'dispatched_at',
        'received_by',
        'received_at',
        'notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function sourceBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(StockTransferReceipt::class);
    }
}
