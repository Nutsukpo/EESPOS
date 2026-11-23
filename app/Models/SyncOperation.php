<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncOperation extends Model
{
    protected $fillable = [
        'business_id',
        'device_id',
        'operation_uuid',
        'operation_type',
        'payload',
        'status',
        'processed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}