<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jul 2024 20:42:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $shipping_id
 * @property int $number_historic_assets
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Shipping $shipping
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingStats query()
 * @mixin \Eloquent
 */
class ShippingStats extends Model
{
    protected $table = 'shipping_stats';

    protected $guarded = [];

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

}