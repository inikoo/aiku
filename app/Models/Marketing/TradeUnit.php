<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:15:50 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Models\Inventory\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\TradeUnit
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string|null $barcode
 * @property float|null $weight
 * @property array|null $dimensions
 * @property string|null $type unit type
 * @property int|null $image_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @method static Builder|TradeUnit newModelQuery()
 * @method static Builder|TradeUnit newQuery()
 * @method static Builder|TradeUnit onlyTrashed()
 * @method static Builder|TradeUnit query()
 * @method static Builder|TradeUnit withTrashed()
 * @method static Builder|TradeUnit withoutTrashed()
 * @mixin \Eloquent
 */
class TradeUnit extends Model
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;

    protected $casts = [
        'data'       => 'array',
        'dimensions' => 'array'
    ];

    protected $attributes = [
        'data'       => '{}',
        'dimensions' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class);
    }


}
