<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:13:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

 namespace App\Models\Manufacturing;

use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Models\Inventory\Location;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Inventory\LocationRawMaterial
 *
 * @property int $id
 * @property int $raw_material_stock_id
 * @property int $location_id
 * @property string $quantity in units
 * @property RawMaterialTypeEnum $type
 * @property int|null $picking_priority
 * @property string|null $notes
 * @property array $data
 * @property array $settings
 * @property string|null $audited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_raw_material_stock_id
 * @property int|null $source_location_id
 * @property bool $dropshipping_pipe
 * @property-read \App\Models\Inventory\Location $location
 * @property-read \App\Models\Manufacturing\RawMaterial $rawMaterial
 * @method static \Illuminate\Database\Eloquent\Builder|LocationRawMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocationRawMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocationRawMaterial query()
 * @mixin \Eloquent
 */
class LocationRawMaterial extends Pivot
{
    public $incrementing = true;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'type'     => RawMaterialTypeEnum::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
