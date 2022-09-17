<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:11:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Actions\Hydrators\HydrateOrganisation;
use App\Models\Organisations\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Inventory\Warehouse
 *
 * @property int $id
 * @property int $organisation_id
 * @property string $code
 * @property string $name
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $organisation_source_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Inventory\Location[] $locations
 * @property-read int|null $locations_count
 * @property-read \App\Models\Inventory\WarehouseStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Inventory\WarehouseArea[] $warehouseAreas
 * @property-read int|null $warehouse_areas_count
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @method static Builder|Warehouse whereCode($value)
 * @method static Builder|Warehouse whereCreatedAt($value)
 * @method static Builder|Warehouse whereData($value)
 * @method static Builder|Warehouse whereDeletedAt($value)
 * @method static Builder|Warehouse whereId($value)
 * @method static Builder|Warehouse whereName($value)
 * @method static Builder|Warehouse whereOrganisationId($value)
 * @method static Builder|Warehouse whereOrganisationSourceId($value)
 * @method static Builder|Warehouse whereSettings($value)
 * @method static Builder|Warehouse whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read Organisation $organisation
 */
class Warehouse extends Model
{
    protected $casts = [
        'data'     => 'array',
        'settings' => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    protected static function booted()
    {
        static::created(
            function (Warehouse $warehouse) {
                HydrateOrganisation::make()->warehouseStats($warehouse->organisation);
            }
        );
        static::deleted(
            function (Warehouse $warehouse) {
                HydrateOrganisation::make()->warehouseStats($warehouse->organisation);

            }
        );


    }

    public function warehouseAreas(): HasMany
    {
        return $this->hasMany(WarehouseArea::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WarehouseStats::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
