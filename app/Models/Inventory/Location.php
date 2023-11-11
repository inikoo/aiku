<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:15:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\Location\LocationStatusEnum;
use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\Location
 *
 * @property int $id
 * @property string $slug
 * @property int $warehouse_id
 * @property int|null $warehouse_area_id
 * @property LocationStatusEnum $status
 * @property string $code
 * @property string $stock_value
 * @property bool $is_empty
 * @property float|null $max_weight
 * @property float|null $max_volume
 * @property array $data
 * @property Carbon|null $audited_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read array $es_audits
 * @property-read Collection<int, \App\Models\Inventory\LostAndFoundStock> $lostAndFoundStocks
 * @property-read \App\Models\Inventory\LocationStats|null $stats
 * @property-read Collection<int, \App\Models\Inventory\Stock> $stocks
 * @property-read UniversalSearch|null $universalSearch
 * @property-read \App\Models\Inventory\Warehouse $warehouse
 * @property-read \App\Models\Inventory\WarehouseArea|null $warehouseArea
 * @method static \Database\Factories\Inventory\LocationFactory factory($count = null, $state = [])
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static Builder|Location onlyTrashed()
 * @method static Builder|Location query()
 * @method static Builder|Location withTrashed()
 * @method static Builder|Location withoutTrashed()
 * @mixin Eloquent
 */
class Location extends Model implements Auditable
{
    use SoftDeletes;
    use HasSlug;

    use HasUniversalSearch;
    use HasFactory;
    use HasHistory;

    protected $casts = [
        'data'       => 'array',
        'audited_at' => 'datetime',
        'status'     => LocationStatusEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }


    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseArea(): BelongsTo
    {
        return $this->belongsTo(WarehouseArea::class);
    }


    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class)->using(LocationStock::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(LocationStats::class);
    }

    public function lostAndFoundStocks(): HasMany
    {
        return $this->hasMany(LostAndFoundStock::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
