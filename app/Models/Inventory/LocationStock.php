<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:13:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Actions\Inventory\Location\HydrateLocation;
use App\Actions\Inventory\Stock\HydrateStock;
use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Inventory\LocationStock
 *
 * @property int $id
 * @property int $stock_id
 * @property int $location_id
 * @property string $quantity
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
 * @property-read \App\Models\Inventory\Location $location
 * @property-read \App\Models\Inventory\Stock $stock
 * @method static Builder|LocationStock newModelQuery()
 * @method static Builder|LocationStock newQuery()
 * @method static Builder|LocationStock query()
 * @mixin \Eloquent
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

    protected static function booted(): void
    {
        static::created(
            function (LocationStock $locationStock) {
                HydrateLocation::make()->stocks($locationStock->location);
                HydrateStock::run($locationStock->stock);
            }
        );
        static::deleted(
            function (LocationStock $locationStock) {
                HydrateLocation::make()->stocks($locationStock->location);
                HydrateStock::run($locationStock->stock);
            }
        );
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
