<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 17:02:34 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Enums\Market\Rental\RentalStateEnum;
use App\Enums\Market\Rental\RentalTypeEnum;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Market\Rental
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
 * @property array $data
 * @property int $number_historic_outerables
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property RentalTypeEnum $type
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Market\HistoricOuterable> $historicRecords
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Market\Product|null $product
 * @property-read \App\Models\Market\RentalSalesStats|null $salesStats
 * @property-read \App\Models\Market\Shop|null $shop
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
        'state'                  => RentalStateEnum::class,
        'type'                   => RentalTypeEnum::class,
        'status'                 => 'boolean',
        'data'                   => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    public function salesStats(): HasOne
    {
        return $this->hasOne(RentalSalesStats::class);
    }

    public function historicRecords(): MorphMany
    {
        return $this->morphMany(HistoricOuterable::class, 'outerable');
    }
}
