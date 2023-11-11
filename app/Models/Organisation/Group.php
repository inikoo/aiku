<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Organisation;

use App\Models\Assets\Currency;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Multitenancy\OrganisationCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Organisation\Group
 *
 * @property int $id
 * @property int|null $owner_id Organisation who owns this model
 * @property string $ulid
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property int $currency_id
 * @property int $number_organisations
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Currency $currency
 * @property-read \App\Models\Organisation\Organisation|null $owner
 * @property-read \App\Models\Organisation\GroupProcurementStats|null $procurementStats
 * @property-read OrganisationCollection<int, \App\Models\Organisation\Organisation> $organisations
 * @method static \Database\Factories\Organisation\GroupFactory factory($count = null, $state = [])
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group onlyTrashed()
 * @method static Builder|Group query()
 * @method static Builder|Group withTrashed()
 * @method static Builder|Group withoutTrashed()
 * @mixin Eloquent
 */
class Group extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasFactory;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function schema(): string
    {
        return 'aiku_grp_'.preg_replace('/-/', '_', $this->slug);
    }

    public function procurementStats(): HasOne
    {
        return $this->hasOne(GroupProcurementStats::class);
    }

    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'group_id');
    }
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'owner_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
