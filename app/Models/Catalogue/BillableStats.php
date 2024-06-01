<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:41:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Catalogue\BillableStats
 *
 * @property int $id
 * @property int $product_id
 * @property int $number_outers
 * @property int $number_outers_available
 * @property int $number_outers_state_in_process
 * @property int $number_outers_state_active
 * @property int $number_outers_state_discontinuing
 * @property int $number_outers_state_discontinued
 * @property int $number_historic_outerables
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Billable $product
 * @method static Builder|BillableStats newModelQuery()
 * @method static Builder|BillableStats newQuery()
 * @method static Builder|BillableStats query()
 * @mixin Eloquent
 */
class BillableStats extends Model
{
    protected $table = 'product_stats';

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Billable::class);
    }
}
