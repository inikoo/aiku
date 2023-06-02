<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 14:23:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use App\Models\Central\CentralMedia;
use App\Models\Tenancy\Tenant;
use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Auth\GroupUser
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property string|null $name
 * @property string|null $about
 * @property bool $status
 * @property int|null $media_id
 * @property array|null $data
 * @property int $number_users
 * @property int $number_active_users
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read CentralMedia|null $avatar
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\GroupMedia> $media
 * @property-read \Spatie\Multitenancy\TenantCollection<int, Tenant> $tenants
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\User> $users
 * @method static Builder|GroupUser newModelQuery()
 * @method static Builder|GroupUser newQuery()
 * @method static Builder|GroupUser onlyTrashed()
 * @method static Builder|GroupUser query()
 * @method static Builder|GroupUser withTrashed()
 * @method static Builder|GroupUser withoutTrashed()
 * @mixin \Eloquent
 */
class GroupUser extends Model implements HasMedia
{
    use HasSlug;
    use UsesGroupConnection;
    use InteractsWithMedia;
    use SoftDeletes;

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

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
        //        return $this->BelongsToMany(User::class)
        //            ->using(GroupUserUser::class)
        //            ->withPivot(['user_id']);
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(CentralMedia::class, 'id', 'media_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->BelongsToMany(Tenant::class)->using(GroupUserTenant::class)
            ->withPivot('user_id', 'data')
            ->withTimestamps();
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
