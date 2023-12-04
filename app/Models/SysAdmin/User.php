<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:30:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Actions\Helpers\Images\GetPictureSources;
use App\Enums\Auth\User\UserAuthTypeEnum;
use App\Helpers\ImgProxy\Image;
use App\Models\Assets\Language;
use App\Models\Media\Media;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasRoles;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\WithPushNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SysAdmin\User
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property bool $status
 * @property string $username
 * @property mixed|null $password
 * @property string|null $type same as parent_type excluding Organisation, for use in UI
 * @property UserAuthTypeEnum $auth_type
 * @property string|null $contact_name no-normalised depends on parent
 * @property string|null $email mirror group_users.email
 * @property string|null $about
 * @property int|null $parent_id
 * @property string|null $parent_type
 * @property string|null $remember_token
 * @property array $data
 * @property array $settings
 * @property int $language_id
 * @property int|null $avatar_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property int|null $source_id
 * @property string|null $legacy_password source password
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read Media|null $avatar
 * @property-read \App\Models\Notifications\FcmToken|null $fcmToken
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notifications\FcmToken> $fcmTokens
 * @property-read Group $group
 * @property-read Language $language
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read \App\Models\SysAdmin\UserStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Database\Factories\SysAdmin\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements HasMedia, Auditable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use HasFactory;
    use HasHistory;
    use WithPushNotifications;
    use HasUniversalSearch;
    use InteractsWithMedia;
    use HasSlug;

    protected $guarded = [
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'data'      => 'array',
        'settings'  => 'array',
        'status'    => 'boolean',
        'auth_type' => UserAuthTypeEnum::class,
        'password'  => 'hashed',
    ];


    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return head(explode('@', trim($this->username)));
            })
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(16);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'avatar_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function avatarImageSources($width = 0, $height = 0)
    {
        if($this->avatar) {
            $avatarThumbnail = (new Image())->make($this->avatar->getImgProxyFilename())->resize($width, $height);
            return GetPictureSources::run($avatarThumbnail);
        }
        return null;
    }

    public function routeNotificationForFcm(): array
    {
        return $this->fcmTokens->pluck('fcm_token')->toArray();
    }


    public function parent(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }


    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(UserStats::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }




}
