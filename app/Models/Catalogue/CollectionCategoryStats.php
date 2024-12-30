<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 16:29:00 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $collection_category_id
 * @property int $number_collections
 * @property int $number_products
 * @property int $number_current_products state: active+discontinuing
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_products_status_in_process
 * @property int $number_products_status_for_sale
 * @property int $number_products_status_not_for_sale
 * @property int $number_products_status_out_of_stock
 * @property int $number_products_status_discontinued
 * @property int $number_products_trade_config_auto
 * @property int $number_products_trade_config_force_offline
 * @property int $number_products_trade_config_force_out_of_stock
 * @property int $number_products_trade_config_force_for_sale
 * @property int $number_rentals
 * @property int $number_rentals_state_in_process
 * @property int $number_rentals_state_active
 * @property int $number_rentals_state_discontinued
 * @property int $number_services
 * @property int $number_services_state_in_process
 * @property int $number_services_state_active
 * @property int $number_services_state_discontinued
 * @property int $number_subscriptions
 * @property int $number_subscriptions_state_in_process
 * @property int $number_subscriptions_state_active
 * @property int $number_subscriptions_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategoryStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategoryStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionCategoryStats query()
 * @mixin \Eloquent
 */
class CollectionCategoryStats extends Model
{
    protected $table = 'collection_category_stats';

    protected $guarded = [];
}
