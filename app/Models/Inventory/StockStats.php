<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:14:53 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Inventory\StockStats
 *
 * @property int $id
 * @property int $stock_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Inventory\Stock $stock
 * @method static Builder|StockStats newModelQuery()
 * @method static Builder|StockStats newQuery()
 * @method static Builder|StockStats query()
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
