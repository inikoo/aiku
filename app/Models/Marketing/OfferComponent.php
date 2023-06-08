<?php

namespace App\Models\Marketing;

use Database\Factories\Marketing\OfferComponentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\OfferComponent
 *
 * @property int $id
 * @property int $offer_campaign_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property array $data
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static OfferComponentFactory factory($count = null, $state = [])
 * @method static Builder|OfferComponent newModelQuery()
 * @method static Builder|OfferComponent newQuery()
 * @method static Builder|OfferComponent onlyTrashed()
 * @method static Builder|OfferComponent query()
 * @method static Builder|OfferComponent withTrashed()
 * @method static Builder|OfferComponent withoutTrashed()
 * @mixin Eloquent
 */
class OfferComponent extends Model
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
}
