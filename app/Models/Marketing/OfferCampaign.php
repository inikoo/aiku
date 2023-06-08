<?php

namespace App\Models\Marketing;

use Database\Factories\Marketing\OfferCampaignFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\OfferCampaign
 *
 * @property int $id
 * @property int $shop_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property array $data
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, OfferComponent> $offerComponent
 * @property-read Collection<int, Offer> $offers
 * @method static OfferCampaignFactory factory($count = null, $state = [])
 * @method static Builder|OfferCampaign newModelQuery()
 * @method static Builder|OfferCampaign newQuery()
 * @method static Builder|OfferCampaign onlyTrashed()
 * @method static Builder|OfferCampaign query()
 * @method static Builder|OfferCampaign withTrashed()
 * @method static Builder|OfferCampaign withoutTrashed()
 * @mixin Eloquent
 */
class OfferCampaign extends Model
{
    use SoftDeletes;
    use UsesTenantConnection;
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
