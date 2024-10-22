<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 21:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Discounts;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Deals\OfferComponent
 *
 * @property int $id
 * @property int $offer_campaign_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\Discounts\OfferComponentFactory factory($count = null, $state = [])
 * @method static Builder<static>|OfferComponent newModelQuery()
 * @method static Builder<static>|OfferComponent newQuery()
 * @method static Builder<static>|OfferComponent onlyTrashed()
 * @method static Builder<static>|OfferComponent query()
 * @method static Builder<static>|OfferComponent withTrashed()
 * @method static Builder<static>|OfferComponent withoutTrashed()
 * @mixin Eloquent
 */
class OfferComponent extends Model
{
    use SoftDeletes;

    use HasSlug;
    use HasFactory;

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}'
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(6);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
