<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 14:42:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Discounts;

use App\Enums\Discounts\OfferCampaign\OfferCampaignStateEnum;
use App\Enums\Discounts\OfferCampaign\OfferCampaignTypeEnum;
use App\Models\Traits\InShop;
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
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property OfferCampaignStateEnum $state
 * @property bool $status
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property OfferCampaignTypeEnum $type
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discounts\OfferComponent> $offerComponent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discounts\Offer> $offers
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
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
    use InShop;


    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'state'    => OfferCampaignStateEnum::class,
        'type'     => OfferCampaignTypeEnum::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $slug = $this->code.' '.$this->shop->code;
                return $slug;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
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
