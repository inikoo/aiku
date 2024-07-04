<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Feb 2024 17:55:39 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Actions\CRM\WebUser\SendLinkResetPassword;
use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasEmail;
use App\Models\Traits\HasImage;
use App\Models\Traits\InCustomer;
use App\Models\Traits\IsUserable;
use App\Models\Web\Website;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\CRM\WebUser
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int $website_id
 * @property int $customer_id
 * @property string $slug
 * @property bool $is_root
 * @property string $type
 * @property bool $status
 * @property string $username
 * @property string|null $email
 * @property string|null $email_verified_at
 * @property string|null $password
 * @property WebUserAuthTypeEnum $auth_type
 * @property string|null $remember_token
 * @property int $number_api_tokens
 * @property string|null $about
 * @property array $data
 * @property array $settings
 * @property bool $reset_password
 * @property int $language_id
 * @property int|null $image_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property WebUserTypeEnum $state
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read Group $group
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read \App\Models\Helpers\Language $language
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read Organisation $organisation
 * @property-read Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read Shop|null $shop
 * @property-read \App\Models\CRM\WebUserStats|null $stats
 * @property-read Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read Website $website
 * @method static Builder|WebUser newModelQuery()
 * @method static Builder|WebUser newQuery()
 * @method static Builder|WebUser onlyTrashed()
 * @method static Builder|WebUser permission($permissions, $without = false)
 * @method static Builder|WebUser query()
 * @method static Builder|WebUser withTrashed()
 * @method static Builder|WebUser withoutPermission($permissions)
 * @method static Builder|WebUser withoutTrashed()
 * @mixin Eloquent
 */
class WebUser extends Authenticatable implements HasMedia, Auditable
{
    use IsUserable;
    use HasPermissions;
    use HasEmail;
    use HasImage;
    use InCustomer;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $slug = $this->username;
                if (filter_var($this->username, FILTER_VALIDATE_EMAIL)) {
                    $slug = strstr($this->username, '@', true);
                }

                return $slug;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(12);
    }

    protected $guarded = [
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [

        'data'      => 'array',
        'settings'  => 'array',
        'state'     => WebUserTypeEnum::class,
        'auth_type' => WebUserAuthTypeEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function sendPasswordResetNotification($token): void
    {
        SendLinkResetPassword::run($token, $this);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(WebUserStats::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
