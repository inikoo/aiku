<?php

namespace App\Models;

use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\Traits\InCustomer;
use Illuminate\Database\Eloquent\Model;
use Osiset\ShopifyApp\Traits\ShopModel;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $customer_id
 * @property string $slug
 * @property bool $status
 * @property string $name
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property WebUserTypeEnum $state
 * @property WebUserAuthTypeEnum $auth_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Osiset\ShopifyApp\Storage\Models\Charge> $charges
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \Osiset\ShopifyApp\Storage\Models\Plan|null $plan
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser withoutTrashed()
 * @mixin \Eloquent
 */
class WooCommerceUser extends Model
{
    use InCustomer;
    use ShopModel;

    protected $guarded = [];

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
}
