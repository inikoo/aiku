<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 17:28:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $shop_id
 * @property int $number_customer_clients
 * @property int $number_current_customer_clients
 * @property int $number_portfolios
 * @property int $number_current_portfolios
 * @property int $number_products
 * @property int $number_current_products
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ShopDropshippingStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopDropshippingStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopDropshippingStat query()
 * @mixin \Eloquent
 */
class ShopDropshippingStat extends Model
{
}
