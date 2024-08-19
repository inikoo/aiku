<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 11:53:46 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Dropshipping;

use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;
use App\Models\ShopifyUserHasProduct;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasEmail;
use App\Models\Traits\HasImage;
use App\Models\Traits\InCustomer;
use App\Models\Traits\IsUserable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Osiset\ShopifyApp\Contracts\ShopModel as IShopModel;
use Osiset\ShopifyApp\Traits\ShopModel;
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
 * @property int $customer_id
 * @property string $slug
 * @property bool $status
 * @property string $name
 * @property bool $shopify_grandfathered
 * @property string|null $shopify_namespace
 * @property bool $shopify_freemium
 * @property int|null $plan_id
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
 * @property string|null $password_updated_at
 * @property int|null $theme_support_level
 * @property WebUserTypeEnum $state
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Collection<int, \Osiset\ShopifyApp\Storage\Models\Charge> $charges
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read Group $group
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read \App\Models\Helpers\Language $language
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read Organisation $organisation
 * @property-read Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Osiset\ShopifyApp\Storage\Models\Plan|null $plan
 * @property-read Shop|null $shop
 * @property-read Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read WebUser|null $webUser
 * @method static Builder|ShopifyUser newModelQuery()
 * @method static Builder|ShopifyUser newQuery()
 * @method static Builder|ShopifyUser onlyTrashed()
 * @method static Builder|ShopifyUser permission($permissions, $without = false)
 * @method static Builder|ShopifyUser query()
 * @method static Builder|ShopifyUser withTrashed()
 * @method static Builder|ShopifyUser withoutPermission($permissions)
 * @method static Builder|ShopifyUser withoutTrashed()
 * @mixin Eloquent
 */
class ShopifyUser extends Authenticatable implements HasMedia, Auditable, IShopModel
{
    use IsUserable;
    use HasPermissions;
    use HasEmail;
    use HasImage;
    use InCustomer;
    use ShopModel;

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

    protected $guarded = [
    ];

    public function generateTags(): array
    {
        return ['crm','websites'];
    }

    protected array $auditInclude = [
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class)->using(ShopifyUserHasProduct::class)
            ->withTimestamps();
    }
}
