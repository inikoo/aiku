<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 15:20:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Inventory\OrgStockFamily;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\OrgStockFamilyStats
 *
 * @property int $id
 * @property int $org_stock_family_id
 * @property int $number_org_stocks
 * @property int $number_org_stocks_state_in_process
 * @property int $number_org_stocks_state_active
 * @property int $number_org_stocks_state_discontinuing
 * @property int $number_org_stocks_state_discontinued
 * @property int $number_org_stocks_quantity_status_excess
 * @property int $number_org_stocks_quantity_status_ideal
 * @property int $number_org_stocks_quantity_status_low
 * @property int $number_org_stocks_quantity_status_critical
 * @property int $number_org_stocks_quantity_status_out_of_stock
 * @property int $number_org_stocks_quantity_status_error
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read OrgStockFamily $orgStockFamily
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStockFamilyStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStockFamilyStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStockFamilyStats query()
 * @mixin \Eloquent
 */
class OrgStockFamilyStats extends Model
{
    protected $table = 'org_stock_family_stats';

    protected $guarded = [];


    public function orgStockFamily(): BelongsTo
    {
        return $this->belongsTo(OrgStockFamily::class);
    }

}
