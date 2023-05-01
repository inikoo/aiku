<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Tenancy\Group
 *
 * @property int $id
 * @property int|null $owner_id Tenant who owns this model
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property int $currency_id
 * @property int $number_tenants
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Currency $currency
 * @property-read \App\Models\Tenancy\Tenant|null $owner
 * @property-read \Spatie\Multitenancy\TenantCollection<int, \App\Models\Tenancy\Tenant> $tenants
 * @method static \Database\Factories\Tenancy\GroupFactory factory($count = null, $state = [])
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group onlyTrashed()
 * @method static Builder|Group query()
 * @method static Builder|Group withTrashed()
 * @method static Builder|Group withoutTrashed()
 * @mixin \Eloquent
 */
class Group extends Model
{
    use UsesLandlordConnection;
    use SoftDeletes;
    use HasSlug;
    use HasFactory;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
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


    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'group_id');
    }
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'owner_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
