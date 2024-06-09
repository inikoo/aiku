<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 12:59:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use App\Models\Helpers\UniversalSearch;
use App\Models\Inventory\OrgStockFamily;
use App\Models\SysAdmin\Group;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SupplyChain\StockFamily
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property StockFamilyStateEnum $state
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_slug
 * @property string|null $source_id
 * @property-read Group $group
 * @property-read Collection<int, OrgStockFamily> $orgStockFamilies
 * @property-read \App\Models\SupplyChain\StockFamilyStats|null $stats
 * @property-read Collection<int, \App\Models\SupplyChain\Stock> $stocks
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\SupplyChain\StockFamilyFactory factory($count = null, $state = [])
 * @method static Builder|StockFamily newModelQuery()
 * @method static Builder|StockFamily newQuery()
 * @method static Builder|StockFamily onlyTrashed()
 * @method static Builder|StockFamily query()
 * @method static Builder|StockFamily withTrashed()
 * @method static Builder|StockFamily withoutTrashed()
 * @mixin Eloquent
 */
class StockFamily extends Model
{
    use HasSlug;
    use SoftDeletes;

    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'data'  => 'array',
        'state' => StockFamilyStateEnum::class,

    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->slugsShouldBeNoLongerThan(32)
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(StockFamilyStats::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function orgStockFamilies(): HasMany
    {
        return $this->hasMany(OrgStockFamily::class);
    }

}
