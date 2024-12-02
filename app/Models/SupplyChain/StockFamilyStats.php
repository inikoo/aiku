<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 12:59:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\SupplyChain\StockFamilyStats
 *
 * @property int $id
 * @property int $stock_family_id
 * @property int $number_stocks
 * @property int $number_current_stocks active + discontinuing
 * @property int $number_stocks_state_in_process
 * @property int $number_stocks_state_active
 * @property int $number_stocks_state_discontinuing
 * @property int $number_stocks_state_discontinued
 * @property int $number_stocks_state_suspended
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SupplyChain\StockFamily $stockFamily
 * @method static Builder<static>|StockFamilyStats newModelQuery()
 * @method static Builder<static>|StockFamilyStats newQuery()
 * @method static Builder<static>|StockFamilyStats query()
 * @mixin Eloquent
 */
class StockFamilyStats extends Model
{
    protected $table = 'stock_family_stats';

    protected $guarded = [];


    public function stockFamily(): BelongsTo
    {
        return $this->belongsTo(StockFamily::class);
    }
}
