<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'qr_code',
        'image',
        'price',
        'cost_price',
        'quantity',
        'category_id',
        'supplier_id',
        'unit',
        'weight',
        'dimensions',
        'min_stock_level',
        'reorder_quantity',
        'track_batches',
        'track_expiry',
        'abc_class',
        'tax_rate',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'track_batches' => 'boolean',
        'track_expiry' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(ProductWarehouseStock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getTotalCostAttribute(): float
    {
        return $this->cost_price ? $this->cost_price * $this->quantity : 0;
    }
}
