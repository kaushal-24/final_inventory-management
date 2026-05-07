<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'lot_number',
        'manufactured_date',
        'expiry_date',
        'quantity',
        'quantity_available',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'manufactured_date' => 'date',
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && now()->gt($this->expiry_date);
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && now()->addDays($days)->gt($this->expiry_date);
    }
}
