<?php

namespace App\Models;

use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\Catalogue\Product;
use App\Models\Ordering\Order;
use App\Models\Traits\InCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
 * @property array<array-key, mixed> $data
 * @property array<array-key, mixed> $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property WebUserTypeEnum $state
 * @property WebUserAuthTypeEnum $auth_type
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WooCommerceUser query()
 * @mixin \Eloquent
 */
class WooCommerceUser extends Model
{
    use InCustomer;

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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shopify_user_has_products')
            ->withTimestamps();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'shopify_user_has_fulfilments')
            ->withTimestamps();
    }
}
