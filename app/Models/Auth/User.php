<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:15:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateUsers;
use App\Enums\Auth\User\UserAuthTypeEnum;
use App\Models\Assets\Language;
use App\Models\Organisation\Organisation;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\WithPushNotifications;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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

    public function routeNotificationForFcm(): array
    {
        return $this->fcmTokens->pluck('fcm_token')->toArray();
    }

    public static function boot(): void
    {
        parent::boot();


        static::updated(function ($item) {
            if (!$item->wasRecentlyCreated) {
                if ($item->wasChanged('status')) {
                    OrganisationHydrateUsers::dispatch();
                }
            }
        });
    }


    public function parent(): MorphTo
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->morphTo()->withTrashed();
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(UserStats::class);
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    protected function avatar(): Attribute
    {
        return new Attribute(
            get: fn () => Arr::get($this->data, 'avatar')
        );
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile();
    }
}
