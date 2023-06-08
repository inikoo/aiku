<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 21:48:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\HistoricProduct
 *
 * @property int $id
 * @property string $slug
 * @property bool $status
 * @property int $product_id
 * @property string $price unit price
 * @property string|null $code
 * @property string|null $name
 * @property string|null $units units per outer
 * @property Carbon|null $created_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Product $product
 * @property-read HistoricProductStats|null $stats
 * @method static Builder|HistoricProduct newModelQuery()
 * @method static Builder|HistoricProduct newQuery()
 * @method static Builder|HistoricProduct onlyTrashed()
 * @method static Builder|HistoricProduct query()
 * @method static Builder|HistoricProduct withTrashed()
 * @method static Builder|HistoricProduct withoutTrashed()
 * @mixin Eloquent
 */
class HistoricProduct extends Model
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;

    protected $casts = [
        'status' => 'boolean',
    ];

    public $timestamps = ['created_at'];

    public const UPDATED_AT = null;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(HistoricProductStats::class);
    }
}
