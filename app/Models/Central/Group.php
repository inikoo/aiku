<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Apr 2023 08:28:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Central\Group
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
 * @property-read \Spatie\Multitenancy\TenantCollection<int, \App\Models\Central\Tenant> $tenants
 * @method static \Database\Factories\Central\GroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group withoutTrashed()
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

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'group_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
