<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 10:08:10 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Inventory\StockFamilyStats
 *
 * @property int $id
 * @property int $stock_family_id
 * @property int $number_stocks
 * @property int $number_stocks_state_in_process
 * @property int $number_stocks_state_active
 * @property int $number_stocks_state_discontinuing
 * @property int $number_stocks_state_discontinued
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\StockFamily $stockFamily
 * @method static Builder|StockFamilyStats newModelQuery()
 * @method static Builder|StockFamilyStats newQuery()
 * @method static Builder|StockFamilyStats query()
 * @mixin \Eloquent
 */
class StockFamilyStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'stock_family_stats';

    protected $guarded = [];


    public function stockFamily(): BelongsTo
    {
        return $this->belongsTo(StockFamily::class);
    }
}
