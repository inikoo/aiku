<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:25:23 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\Tenancy\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Central\CentralUser
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property string|null $name
 * @property string|null $about
 * @property int|null $media_id
 * @property array|null $data
 * @property int $number_users
 * @property int $number_active_users
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read CentralMedia|null $avatar
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\User> $users
 * @method static Builder|GroupUser newModelQuery()
 * @method static Builder|GroupUser newQuery()
 * @method static Builder|GroupUser onlyTrashed()
 * @method static Builder|GroupUser query()
 * @method static Builder|GroupUser withTrashed()
 * @method static Builder|GroupUser withoutTrashed()
 * @mixin \Eloquent
 */
class CentralUser extends Model implements HasMedia
{
    use HasSlug;
    use UsesLandlordConnection;
    use InteractsWithMedia;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('username')
            ->saveSlugsTo('username');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            Tenant::class,
            'central_user_tenant',
        )->using(CentralUserTenant::class);
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(CentralMedia::class, 'id', 'media_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(256)
                    ->height(256);
            });
    }
}
