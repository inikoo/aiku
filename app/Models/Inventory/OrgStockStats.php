<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 10:28:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory\OrgStockStats
 *
 * @property int $id
 * @property int $org_stock_id
 * @property int $number_locations
 * @property int $number_org_stock_movements
 * @property int $number_org_stock_movements_type_purchase
 * @property int $number_org_stock_movements_type_return_dispatch
 * @property int $number_org_stock_movements_type_return_picked
 * @property int $number_org_stock_movements_type_return_consumption
 * @property int $number_org_stock_movements_type_picked
 * @property int $number_org_stock_movements_type_location_transfer
 * @property int $number_org_stock_movements_type_found
 * @property int $number_org_stock_movements_type_consumption
 * @property int $number_org_stock_movements_type_write_off
 * @property int $number_org_stock_movements_type_adjustment
 * @property int $number_org_stock_movements_type_associate
 * @property int $number_org_stock_movements_type_disassociate
 * @property int $number_org_stock_movements_flow_in
 * @property int $number_org_stock_movements_flow_out
 * @property int $number_org_stock_movements_flow_no_change
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\OrgStock $orgStock
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockStats query()
 * @mixin \Eloquent
 */
class OrgStockStats extends Model
{
    protected $table = 'org_stock_stats';

    protected $guarded = [];


    public function orgStock(): BelongsTo
    {
        return $this->belongsTo(OrgStock::class);
    }
}
