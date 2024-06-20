<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 17:28:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $group_id
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
 * @method static \Illuminate\Database\Eloquent\Builder|GroupDropshippingStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupDropshippingStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupDropshippingStat query()
 * @mixin \Eloquent
 */
class GroupDropshippingStat extends Model
{
}
