<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 06:46:24 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $collection_id
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
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionStats query()
 * @mixin \Eloquent
 */
class CollectionStats extends Model
{
    protected $table = 'collection_stats';

    protected $guarded = [];
}
