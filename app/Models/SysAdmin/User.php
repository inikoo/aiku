<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:36:03 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateUsers;
use App\Models\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\SysAdmin\User
 *
 * @property int $id
 * @property int $central_user_id
 * @property string $username
 * @property bool $status
 * @property string|null $parent_type
 * @property int|null $parent_id
 * @property string|null $email
 * @property string|null $about
 * @property string|null $remember_token
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $password
 * @property int|null $source_id
 * @property int|null $image_id
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $parent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $parentWithTrashed
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read \App\Models\SysAdmin\UserStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use UsesTenantConnection;


    protected $guarded = [
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'data'         => 'array',
        'settings'     => 'array',
        'status'       => 'boolean'
    ];


    protected $attributes = [
        'data'         => '{}',
        'settings'     => '{}',
    ];


    public static function boot()
    {
        parent::boot();


        static::updated(function ($item) {
            if (!$item->wasRecentlyCreated) {
                if ($item->wasChanged('status')) {
                    TenantHydrateUsers::dispatch(app('currentTenant'));
                }
            }
        });
    }



    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function parentWithTrashed(): MorphTo
    {
        return $this->morphTo('parent')->withTrashed();
    }



    public function stats(): HasOne
    {
        return $this->hasOne(UserStats::class);
    }
    public function getRouteKeyName(): string
    {
        return 'username';
    }
}
