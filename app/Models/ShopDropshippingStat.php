<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 10:25:34 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $shop_id
 * @property int $number_customer_clients
 * @property int $number_current_customer_clients
 * @property int $number_dropshipping_customer_portfolios
 * @property int $number_current_dropshipping_customer_portfolios
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
