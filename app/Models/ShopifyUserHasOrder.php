<?php

namespace App\Models;

use App\Models\Dropshipping\ShopifyUser;
use App\Models\Ordering\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @property int $id
 * @property int $shopify_user_id
 * @property int $order_id
 * @property int|null $shopify_order_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ShopifyUser $shopifyUser
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasOrder query()
 * @mixin \Eloquent
 */
class ShopifyUserHasOrder extends Pivot
{
    protected $table = 'shopify_user_has_orders';

    public function shopifyUser(): BelongsTo
    {
        return $this->belongsTo(ShopifyUser::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
