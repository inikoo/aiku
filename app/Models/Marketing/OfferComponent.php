<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\Marketing\OfferComponentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|OfferComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferComponent onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferComponent withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferComponent withoutTrashed()
 * @mixin \Eloquent
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
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(6);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
