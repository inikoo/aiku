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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inventory\OrgStock $orgStock
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStockStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStockStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStockStats query()
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
