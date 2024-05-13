<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:55:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Enums\Fulfilment\Rental\RentalTypeEnum;
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Models\Catalogue\HistoricOuterable;
use App\Models\Catalogue\IsOuterable;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Catalogue\Rental
 *
 * @property int $id
 * @property bool $status
 * @property RentalStateEnum $state
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $fulfilment_id
 * @property int|null $product_id
 * @property string|null $auto_assign_asset Used for auto assign this rent to this asset
 * @property string|null $auto_assign_asset_type Used for auto assign this rent to this asset type
 * @property string|null $price
 * @property RentalUnitEnum|null $unit
 * @property array $data
 * @property int $number_historic_outerables
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $historic_source_id
 * @property RentalTypeEnum $type
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HistoricOuterable> $historicRecords
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Product|null $product
 * @property-read \App\Models\Fulfilment\RentalSalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Rental newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rental newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rental onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Rental query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rental withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Rental withoutTrashed()
 * @mixin \Eloquent
 */
class Rental extends Model
{
    use SoftDeletes;
    use HasUniversalSearch;
    use IsOuterable;

    protected $guarded = [];

    protected $casts = [
        'state'  => RentalStateEnum::class,
        'type'   => RentalTypeEnum::class,
        'unit'   => RentalUnitEnum::class,
        'status' => 'boolean',
        'data'   => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(RentalSalesIntervals::class);
    }

    public function historicRecords(): MorphMany
    {
        return $this->morphMany(HistoricOuterable::class, 'outerable');
    }
}
