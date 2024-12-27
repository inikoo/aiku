<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:15:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $master_shop_id
 * @property int $number_shops
 * @property int $number_current_shops state=open+closing-down
 * @property int $number_shops_state_in_process
 * @property int $number_shops_state_open
 * @property int $number_shops_state_closing_down
 * @property int $number_shops_state_closed
 * @property int $number_master_departments
 * @property int $number_current_master_departments
 * @property int $number_master_departments_state_in_process
 * @property int $number_master_departments_state_active
 * @property int $number_master_departments_state_discontinuing
 * @property int $number_master_departments_state_discontinued
 * @property int $number_master_sub_departments
 * @property int $number_current_master_sub_departments state: active+discontinuing
 * @property int $number_master_families
 * @property int $number_current_master_families state: active+discontinuing
 * @property int $number_orphan_master_families
 * @property int $number_master_products
 * @property int $number_current_master_products state: active+discontinuing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Goods\MasterShop $masterShop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopStats query()
 * @mixin \Eloquent
 */
class MasterShopStats extends Model
{
    protected $table = 'master_shop_stats';

    protected $guarded = [];

    public function masterShop(): BelongsTo
    {
        return $this->belongsTo(MasterShop::class);
    }
}
