<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:13:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Inventory\LocationStock
 *
 * @property int $id
 * @property int $stock_id
 * @property int $location_id
 * @property string $quantity in units
 * @property LocationStockTypeEnum $type
 * @property int|null $picking_priority
 * @property string|null $notes
 * @property array $data
 * @property array $settings
 * @property string|null $audited_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $source_stock_id
 * @property int|null $source_location_id
 * @property-read Location $location
 * @property-read Stock $stock
 * @method static Builder|LocationStock newModelQuery()
 * @method static Builder|LocationStock newQuery()
 * @method static Builder|LocationStock query()
 * @mixin Eloquent
 */
class LocationStock extends Pivot
{
    use UsesTenantConnection;

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


    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
