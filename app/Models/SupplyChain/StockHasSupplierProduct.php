<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 15 Nov 2024 19:52:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use App\Models\Inventory\OrgStockHasOrgSupplierProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 *
 * @property int $id
 * @property int $stock_id
 * @property int|null $supplier_product_id
 * @property bool $available
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrgStockHasOrgSupplierProduct> $orgStockHasOrgSupplierProducts
 * @property-read \App\Models\SupplyChain\Stock $stock
 * @property-read \App\Models\SupplyChain\SupplierProduct|null $supplierProduct
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockHasSupplierProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockHasSupplierProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockHasSupplierProduct query()
 * @mixin \Eloquent
 */
class StockHasSupplierProduct extends Model
{
    protected $table = 'stock_has_supplier_products';

    protected $casts = [
    ];

    protected $guarded = [];


    public function orgStockHasOrgSupplierProducts(): HasMany
    {
        return $this->hasMany(OrgStockHasOrgSupplierProduct::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }


}
