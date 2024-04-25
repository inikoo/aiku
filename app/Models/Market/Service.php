<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:37:06 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Enums\Market\Service\ServiceStateEnum;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Market\Service
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property bool $status
 * @property ServiceStateEnum $state
 * @property int|null $shop_id
 * @property int|null $product_id
 * @property int $number_historic_outerables
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Market\HistoricOuterable> $historicRecords
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Market\Product|null $product
 * @property-read \App\Models\Market\ServiceSalesStats|null $salesStats
 * @property-read \App\Models\Market\Shop|null $shop
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Service withoutTrashed()
 * @mixin \Eloquent
 */
class Service extends Model
{
    use SoftDeletes;
    use HasUniversalSearch;
    use IsOuterable;

    protected $guarded = [];

    protected $casts = [
        'state'                  => ServiceStateEnum::class,
        'status'                 => 'boolean',
        'data'                   => 'array',

    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    public function salesStats(): HasOne
    {
        return $this->hasOne(ServiceSalesStats::class);
    }


    public function historicRecords(): MorphMany
    {
        return $this->morphMany(HistoricOuterable::class, 'outerable');
    }
}
