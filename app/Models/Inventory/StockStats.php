<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:14:53 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory\StockStats
 *
 * @property int $id
 * @property int $stock_id
 * @property int $number_locations
 * @property string $stock_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\Stock $stock
 * @method static Builder|StockStats newModelQuery()
 * @method static Builder|StockStats newQuery()
 * @method static Builder|StockStats query()
 * @method static Builder|StockStats whereCreatedAt($value)
 * @method static Builder|StockStats whereId($value)
 * @method static Builder|StockStats whereNumberLocations($value)
 * @method static Builder|StockStats whereStockId($value)
 * @method static Builder|StockStats whereStockValue($value)
 * @method static Builder|StockStats whereUpdatedAt($value)
 * @mixin \Eloquent
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
