<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:36:03 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Organisations\Organisation;
use App\Models\Organisations\OrganisationUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\Auth\User
 *
 * @property int $id
 * @property string|null $username
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $facebook_id
 * @property string|null $twitter_id
 * @property string|null $google_id
 * @property string|null $remember_token
 * @property int $number_organisations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Organisations\Organisation[] $organisations
 * @property-read int|null $organisations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFacebookId($value)
 * @method static Builder|User whereGoogleId($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereNumberOrganisations($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereTwitterId($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @mixin \Eloquent
 * @property int|null $organisation_id
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Organisations\Organisation|null $organisation
 * @method static Builder|User whereOrganisationId($value)
 * @property array $data
 * @property array $settings
 * @method static Builder|User whereData($value)
 * @method static Builder|User whereSettings($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static Builder|User permission($permissions)
 * @method static Builder|User role($roles, $guard = null)
 */
class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable;
    use InteractsWithMedia;
    use HasRoles;


    protected $guarded = [
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'data'              => 'array',
        'settings'          => 'array',
    ];

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class)->using(OrganisationUser::class)->withTimestamps();
    }

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    /**
     * Return the current main organisation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->useDisk('public')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(256)
                    ->height(256);
            });
    }

    public function getIULayout(): array
    {
        $navigation = [
            [
                'name'  => 'Home',
                'icon'  => ['fal', 'fa-home'],
                'route' => 'dashboard'
            ]
        ];


        if ($this->can('warehouses.dispatching.pick')) {
            $navigation[] = [
                'name'  => 'Picking',
                'icon'  => ['fal', 'fa-dolly-flatbed-alt'],
                'route' => 'dashboard'
            ];
        }

        if ($this->can('warehouses.dispatching.pack')) {
            $navigation[] = [
                'name'  => 'Packing',
                'icon'  => ['fal', 'fa-conveyor-belt-alt'],
                'route' => 'dashboard'
            ];
        }

        if ($this->can('employees.view')) {
            $navigation[] = [
                'name'  => 'Employees',
                'icon'  => ['fal', 'fa-user-hard-hat'],
                'route' => 'hr.employees.index'
            ];
        }

        if ($this->can('users.view')) {
            $navigation[] = [
                'name'  => 'Users',
                'icon'  => ['fal', 'fa-users'],
                'route' => 'dashboard'
            ];
        }


        return [
            'navigation' => $navigation
        ];
    }

}
