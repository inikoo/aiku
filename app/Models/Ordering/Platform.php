<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Jun 2024 14:00:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\Catalogue\Product;
use App\Models\CRM\Customer;
use App\Models\PlatformStats;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property PlatformTypeEnum $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read PlatformStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder|Platform newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Platform newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Platform query()
 * @mixin \Eloquent
 */
class Platform extends Model
{
    use HasSlug;

    protected $casts = [
        'type' => PlatformTypeEnum::class
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PlatformStats::class);
    }

    public function customers(): MorphToMany
    {
        return $this->morphToMany(Customer::class, 'model', 'model_has_platforms')
            ->withTimestamps();
    }

    public function products(): MorphToMany
    {
        return $this->morphToMany(Product::class, 'model', 'model_has_platforms')
            ->withTimestamps();
    }

    public function orders(): MorphToMany
    {
        return $this->morphToMany(Order::class, 'model', 'model_has_platforms')
            ->withTimestamps();
    }
}
