<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:14:53 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory\StockStats
 *
 * @property int $id
 * @property int $stock_id
 * @property string|null $quantity_status_from
 * @property string|null $quantity_status_upto
 * @property int $number_organisations
 * @property int $number_organisations_state_active
 * @property int $number_organisations_state_discontinuing
 * @property int $number_organisations_state_discontinued
 * @property int $number_organisations_state_suspended
 * @property int $number_organisations_quantity_status_excess
 * @property int $number_organisations_quantity_status_ideal
 * @property int $number_organisations_quantity_status_low
 * @property int $number_organisations_quantity_status_critical
 * @property int $number_organisations_quantity_status_out_of_stock
 * @property int $number_organisations_quantity_status_error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SupplyChain\Stock $stock
 * @method static Builder<static>|StockStats newModelQuery()
 * @method static Builder<static>|StockStats newQuery()
 * @method static Builder<static>|StockStats query()
 * @mixin Eloquent
 */
class StockStats extends Model
{
    protected $table = 'stock_stats';

    protected $guarded = [];


    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
