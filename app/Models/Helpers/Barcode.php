<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 15:17:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\Catalogue\Product;
use App\Models\SupplyChain\Stock;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Helpers\Barcode
 *
 * @property int $id
 * @property string $slug
 * @property string $type
 * @property string $status
 * @property string $number
 * @property string|null $assigned_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Collection<int, Product> $product
 * @property-read Collection<int, Stock> $stock
 * @method static Builder|Barcode newModelQuery()
 * @method static Builder|Barcode newQuery()
 * @method static Builder|Barcode onlyTrashed()
 * @method static Builder|Barcode query()
 * @method static Builder|Barcode withTrashed()
 * @method static Builder|Barcode withoutTrashed()
 * @mixin Eloquent
 */
class Barcode extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('number')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function stock(): MorphToMany
    {
        return $this->morphedByMany(Stock::class, 'barcodeable');
    }

    public function product(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'barcodeable');
    }
}
