<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:13:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Inventory\LocationOrgStock
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $warehouse_id
 * @property int|null $warehouse_area_id
 * @property int $org_stock_id
 * @property int $location_id
 * @property string $quantity in units
 * @property string $value total value based in cost
 * @property string $commercial_value total value based selling price
 * @property LocationStockTypeEnum $type
 * @property int|null $picking_priority
 * @property string|null $notes
 * @property array $data
 * @property array $settings
 * @property string|null $audited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_stock_id
 * @property int|null $source_location_id
 * @property bool $dropshipping_pipe
 * @property-read \App\Models\Inventory\Location $location
 * @property-read \App\Models\Inventory\OrgStock $orgStock
 * @method static \Illuminate\Database\Eloquent\Builder|LocationOrgStock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocationOrgStock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocationOrgStock query()
 * @mixin \Eloquent
 */
class LocationOrgStock extends Model
{
    public $incrementing = true;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'type'     => LocationStockTypeEnum::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];


    public function orgStock(): BelongsTo
    {
        return $this->belongsTo(OrgStock::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
