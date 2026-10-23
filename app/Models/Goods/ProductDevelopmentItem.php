<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Models\Goods;

use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $product_development_id
 * @property int $trade_unit_id
 * @property int|null $supplier_product_id
 * @property string|null $brief the product-specific part of the brief (fragrance, colour, look)
 * @property int $position
 * @property-read ProductDevelopment|null $productDevelopment
 * @property-read TradeUnit|null $tradeUnit
 * @property-read SupplierProduct|null $supplierProduct
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductDevelopmentChecklistItem> $checklistItems
 * @mixin \Eloquent
 */
class ProductDevelopmentItem extends Model
{
    protected $table = 'product_development_items';

    protected $guarded = [];

    public function productDevelopment(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class);
    }

    public function tradeUnit(): BelongsTo
    {
        return $this->belongsTo(TradeUnit::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ProductDevelopmentChecklistItem::class);
    }
}
