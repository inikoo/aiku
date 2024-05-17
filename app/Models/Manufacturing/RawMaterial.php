<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 20:58:52 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Manufacturing;

use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InProduction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property RawMaterialTypeEnum $type
 * @property RawMaterialStateEnum $state
 * @property int $production_id
 * @property int|null $stock_id
 * @property string $code
 * @property string $description
 * @property RawMaterialUnitEnum $unit
 * @property string $unit_cost
 * @property string|null $quantity_on_location
 * @property RawMaterialStockStatusEnum $stock_status
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Manufacturing\Production $production
 * @property-read \App\Models\Manufacturing\RawMaterialStats|null $stats
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|RawMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawMaterial onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RawMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder|RawMaterial withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RawMaterial withoutTrashed()
 * @mixin \Eloquent
 */
class RawMaterial extends Model implements Auditable
{
    use InProduction;
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasHistory;

    protected $guarded = [];

    protected $casts = [
        'data'         => 'array',
        'type'         => RawMaterialTypeEnum::class,
        'state'        => RawMaterialStateEnum::class,
        'unit'         => RawMaterialUnitEnum::class,
        'stock_status' => RawMaterialStockStatusEnum::class,
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function stats(): HasOne
    {
        return $this->hasOne(RawMaterialStats::class);
    }


}
