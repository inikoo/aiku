<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:36:03 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

// use Illuminate\Contracts\SysAdmin\MustVerifyEmail;
use App\Models\HumanResources\Employee;
use App\Models\Organisations\Organisation;
use App\Models\Organisations\OrganisationUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;


/**
 * App\Models\SysAdmin\User
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $facebook_id
 * @property string|null $twitter_id
 * @property string|null $google_id
 * @property string|null $remember_token
 * @property int $number_organisations
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $current_ui_organisation_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Employee[] $employees
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SysAdmin\Guest[] $guests
 * @property-read int|null $guests_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection|Organisation[] $organisations
 * @property-read int|null $organisations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereData($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFacebookId($value)
 * @method static Builder|User whereGoogleId($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereNumberOrganisations($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereSettings($value)
 * @method static Builder|User whereTwitterId($value)
 * @method static Builder|User whereUiCurrentOrganisationId($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @mixin \Eloquent
 * @property-read Organisation|null $currentUiOrganisation
 * @method static Builder|User whereCurrentUiOrganisationId($value)
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


    public function currentUiOrganisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class,'current_ui_organisation_id');
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


    public function employees(): MorphToMany
    {
        return $this->morphedByMany(Employee::class, 'userable','organisation_user')->withTimestamps();

    }

    public function guests(): MorphToMany
    {
        return $this->morphedByMany(Guest::class, 'userable','organisation_user')->withTimestamps();
    }

}
