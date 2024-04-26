<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Apr 2024 17:00:31 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\SysAdmin\GroupMarketStats
 *
 * @property int $id
 * @property int $group_id
 * @property int $number_shops
 * @property int $number_shops_state_in_process
 * @property int $number_shops_state_open
 * @property int $number_shops_state_closing_down
 * @property int $number_shops_state_closed
 * @property int $number_shops_type_b2b
 * @property int $number_shops_type_b2c
 * @property int $number_shops_type_fulfilment
 * @property int $number_shops_type_dropshipping
 * @property int $number_departments
 * @property int $number_current_departments
 * @property int $number_departments_state_in_process
 * @property int $number_departments_state_active
 * @property int $number_departments_state_discontinuing
 * @property int $number_departments_state_discontinued
 * @property int $number_sub_departments
 * @property int $number_current_sub_departments state: active+discontinuing
 * @property int $number_sub_departments_state_in_process
 * @property int $number_sub_departments_state_active
 * @property int $number_sub_departments_state_discontinuing
 * @property int $number_sub_departments_state_discontinued
 * @property int $number_families
 * @property int $number_current_families state: active+discontinuing
 * @property int $number_families_state_in_process
 * @property int $number_families_state_active
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_orphan_families
 * @property int $number_products
 * @property int $number_current_products state: active+discontinuing
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_products_type_physical_good
 * @property int $number_products_type_service
 * @property int $number_products_type_subscription
 * @property int $number_products_type_rental
 * @property int $number_rentals_state_in_process
 * @property int $number_rentals_state_active
 * @property int $number_rentals_state_discontinued
 * @property int $number_services_state_in_process
 * @property int $number_services_state_active
 * @property int $number_services_state_discontinued
 * @property int $number_physical_goods_state_in_process
 * @property int $number_physical_goods_state_active
 * @property int $number_physical_goods_state_discontinuing
 * @property int $number_physical_goods_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMarketStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMarketStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMarketStats query()
 * @mixin \Eloquent
 */
class GroupMarketStats extends Model
{
    protected $table = 'group_market_stats';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
