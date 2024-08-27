<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Aug 2024 17:27:59 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Dropshipping\ShopifyUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @property int $id
 * @property int $shopify_user_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $shopify_product_id
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct query()
 * @mixin \Eloquent
 */
class ShopifyUserHasProduct extends Pivot
{
    protected $table = 'shopify_user_has_products';

    public function shopifyUser(): BelongsTo
    {
        return $this->belongsTo(ShopifyUser::class);
    }
}
