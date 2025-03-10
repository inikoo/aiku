<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 26 Dec 2024 13:24:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockFamilyTimeSeriesRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockFamilyTimeSeriesRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrgStockFamilyTimeSeriesRecord query()
 * @mixin \Eloquent
 */
class OrgStockFamilyTimeSeriesRecord extends Model
{
    protected $table = 'org_stock_family_time_series_records';

    protected $guarded = [];
}
