<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:15:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateUsers;
use App\Models\Tenancy\Tenant;
use App\Models\Traits\HasRoles;
use Database\Factories\Auth\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Auth\User
 *
 * @property int $id
 * @property int $group_user_id
 * @property bool $status
 * @property string $username mirror group_users.username
 * @property string $password mirror group_users.password
 * @property string|null $email mirror group_users.email
 * @property int|null $parent_id
 * @property string|null $parent_type
 * @property string|null $remember_token
 * @property array $data
 * @property array $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read GroupUser|null $groupUser
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read Model|Eloquent $parent
 * @property-read Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read UserStats|null $stats
 * @property-read Tenant $tenant
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @method static UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use UsesTenantConnection;
    use HasFactory;


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


    public static function boot(): void
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
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->morphTo()->withTrashed();
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function groupUser(): BelongsTo
    {
        return $this->belongsTo(GroupUser::class);
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
}
