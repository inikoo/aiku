<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:03:08 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Deals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Deals\OfferCampaign
 *
 * @property int $id
 * @property int $shop_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deals\OfferComponent> $offerComponent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deals\Offer> $offers
 * @method static \Database\Factories\Deals\OfferCampaignFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|OfferCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferCampaign onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferCampaign query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferCampaign withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferCampaign withoutTrashed()
 * @mixin \Eloquent
 */
class OfferCampaign extends Model
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

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function offerComponent(): HasMany
    {
        return $this->hasMany(OfferComponent::class);
    }
}
