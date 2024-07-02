<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 17:28:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $portfolio_id
 * @property int $amount
 * @property int $number_orders
 * @property int $number_ordered_quantity
 * @property int $number_clients
 * @property int|null $last_ordered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PortfolioStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PortfolioStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PortfolioStats query()
 * @mixin \Eloquent
 */
class PortfolioStats extends Model
{
}
