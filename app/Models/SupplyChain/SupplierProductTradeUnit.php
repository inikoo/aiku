<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 12:40:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\SupplierProductTradeUnit
 *
 * @property int $id
 * @property int|null $supplier_product_id
 * @property int|null $trade_unit_id
 * @property float $package_quantity
 * @property float|null $carton_quantity
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SupplyChain\Supplier|null $supplier
 * @method static Builder|SupplierProductTradeUnit newModelQuery()
 * @method static Builder|SupplierProductTradeUnit newQuery()
 * @method static Builder|SupplierProductTradeUnit query()
 * @mixin Eloquent
 */
class SupplierProductTradeUnit extends Pivot
{
    protected $table     = 'supplier_product_trade_unit';
    public $incrementing = true;

    protected $guarded = [];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
